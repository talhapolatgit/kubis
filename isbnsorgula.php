<?php
$isbn = isset($_GET['isbn']) ? trim($_GET['isbn']) : '';
$imageUrl = null;
$imageSource = null;
$error = null;
$bookTitle = null;
$bookAuthor = null;
$bookPublisher = null;

function cleanISBN($input) {
    return preg_replace('/[-\s]/', '', $input);
}

function convertISBN13to10($isbn13) {
    if (strlen($isbn13) !== 13) return $isbn13;
    
    $digits = substr($isbn13, 3, 9);
    
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += intval($digits[$i]) * (10 - $i);
    }
    $checkDigit = (11 - ($sum % 11)) % 11;
    $checkChar = $checkDigit === 10 ? 'X' : strval($checkDigit);
    
    return $digits . $checkChar;
}

function fetchUrl($url) {
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    return @file_get_contents($url, false, $context);
}

function checkImageExists($url) {
    $headers = @get_headers($url, 1);
    if ($headers === false) return false;
    
    // HTTP 200 OK kontrolü
    if (strpos($headers[0], '200') === false) return false;
    
    // Content-Length kontrolü (Amazon boş resim döndürürse küçük boyutlu olur)
    if (isset($headers['Content-Length'])) {
        $size = is_array($headers['Content-Length']) ? end($headers['Content-Length']) : $headers['Content-Length'];
        if ((int)$size < 1000) return false; // 1KB'dan küçükse geçersiz
    }
    
    return true;
}

// Google Books API'den kitap bilgisi al
function getBookInfoFromGoogle($isbn) {
    $url = "https://www.googleapis.com/books/v1/volumes?q=isbn:" . urlencode($isbn);
    $response = fetchUrl($url);
    
    if ($response === false) return null;
    
    $data = json_decode($response, true);
    
    if (isset($data['totalItems']) && $data['totalItems'] > 0 && isset($data['items'][0]['volumeInfo'])) {
        $volumeInfo = $data['items'][0]['volumeInfo'];
        $imageLinks = $volumeInfo['imageLinks'] ?? null;
        
        $coverUrl = null;
        if ($imageLinks) {
            // En yüksek kaliteli resmi seç
            $coverUrl = $imageLinks['extraLarge'] ?? $imageLinks['large'] ?? $imageLinks['medium'] ?? $imageLinks['thumbnail'] ?? null;
            // HTTP'yi HTTPS'e çevir
            if ($coverUrl) {
                $coverUrl = str_replace('http://', 'https://', $coverUrl);
                // Zoom parametresini artır
                $coverUrl = preg_replace('/zoom=\d/', 'zoom=3', $coverUrl);
            }
        }
        
        return [
            'title' => $volumeInfo['title'] ?? null,
            'authors' => $volumeInfo['authors'] ?? [],
            'publisher' => $volumeInfo['publisher'] ?? null,
            'coverUrl' => $coverUrl
        ];
    }
    
    return null;
}

// Open Library API'den kitap bilgisi al
function getBookInfoFromOpenLibrary($isbn) {
    $url = "https://openlibrary.org/api/books?bibkeys=ISBN:" . urlencode($isbn) . "&format=json&jscmd=data";
    $response = fetchUrl($url);
    
    if ($response === false) return null;
    
    $data = json_decode($response, true);
    $bookData = $data["ISBN:{$isbn}"] ?? null;
    
    if ($bookData) {
        $coverUrl = $bookData['cover']['large'] ?? $bookData['cover']['medium'] ?? null;
        if (!$coverUrl) {
            $coverUrl = "https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg";
        }
        
        $authors = [];
        if (isset($bookData['authors'])) {
            foreach ($bookData['authors'] as $author) {
                $authors[] = $author['name'];
            }
        }
        
        $publishers = [];
        if (isset($bookData['publishers'])) {
            foreach ($bookData['publishers'] as $publisher) {
                $publishers[] = $publisher['name'];
            }
        }
        
        return [
            'title' => $bookData['title'] ?? null,
            'authors' => $authors,
            'publisher' => !empty($publishers) ? $publishers[0] : null,
            'coverUrl' => $coverUrl
        ];
    }
    
    return null;
}

if (!empty($isbn)) {
    $cleanedISBN = cleanISBN($isbn);
    
    if (strlen($cleanedISBN) !== 10 && strlen($cleanedISBN) !== 13) {
        $error = "ISBN numarası 10 veya 13 haneli olmalıdır";
    } else {
        $isbn10 = strlen($cleanedISBN) === 13 ? convertISBN13to10($cleanedISBN) : $cleanedISBN;
        
        // 1. Kitap bilgilerini Google'dan al
        $bookInfo = getBookInfoFromGoogle($cleanedISBN);
        
        // 2. Google'da bulunamazsa Open Library'den dene
        if (!$bookInfo || (!$bookInfo['title'] && empty($bookInfo['authors']))) {
            $openLibraryInfo = getBookInfoFromOpenLibrary($cleanedISBN);
            if ($openLibraryInfo) {
                $bookInfo = $bookInfo ?? [];
                $bookInfo['title'] = $bookInfo['title'] ?? $openLibraryInfo['title'];
                $bookInfo['authors'] = !empty($bookInfo['authors']) ? $bookInfo['authors'] : $openLibraryInfo['authors'];
                $bookInfo['publisher'] = $bookInfo['publisher'] ?? $openLibraryInfo['publisher'];
                if (!isset($bookInfo['coverUrl'])) {
                    $bookInfo['coverUrl'] = $openLibraryInfo['coverUrl'];
                }
            }
        }
        
        if ($bookInfo) {
            $bookTitle = $bookInfo['title'];
            $bookAuthor = !empty($bookInfo['authors']) ? implode(', ', $bookInfo['authors']) : null;
            $bookPublisher = $bookInfo['publisher'];
        }
        
        // 3. Kapak resmi: Önce Amazon dene
        $amazonUrl = "https://images-na.ssl-images-amazon.com/images/P/{$isbn10}.01.LZZZZZZZ.jpg";
        if (checkImageExists($amazonUrl)) {
            $imageUrl = $amazonUrl;
            $imageSource = "Amazon";
        } 
        // 4. Amazon'da yoksa Google'dan dene
        elseif ($bookInfo && !empty($bookInfo['coverUrl'])) {
            $imageUrl = $bookInfo['coverUrl'];
            $imageSource = "Google Books";
        } 
        // 5. Google'da da yoksa Open Library'den dene
        else {
            $openLibraryCover = "https://covers.openlibrary.org/b/isbn/{$cleanedISBN}-L.jpg";
            $imageUrl = $openLibraryCover;
            $imageSource = "Open Library";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISBN Kitap Kapağı Bulucu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-amber-50 to-orange-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl shadow-xl border border-amber-200 overflow-hidden">
            <div class="p-6 border-b border-amber-100">
                <div class="flex items-center gap-2 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-amber-600">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                    </svg>
                    <h1 class="text-xl font-bold text-amber-900">ISBN Kitap Kapağı Bulucu</h1>
                </div>
                <p class="text-amber-700 text-sm">ISBN numarası girerek kitap kapağını Amazon'dan bulun</p>
            </div>
            
            <div class="p-6 space-y-6">
                <form method="GET" class="space-y-4">
                    <div>
                        <label for="isbn" class="block text-sm font-medium text-amber-800 mb-2">ISBN Numarası</label>
                        <div class="flex gap-2">
                            <input 
                                type="text" 
                                id="isbn"
                                name="isbn" 
                                value="<?php echo htmlspecialchars($isbn); ?>"
                                placeholder="978-0-14-143951-8"
                                class="flex-1 px-4 py-2 border border-amber-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                            >
                            <button 
                                type="submit"
                                class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="m21 21-4.3-4.3"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-amber-600 mt-1">ISBN-10 veya ISBN-13 formatında girebilirsiniz</p>
                    </div>
                </form>

                <?php if ($error): ?>
                <div class="flex items-center gap-2 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" x2="12" y1="8" y2="12"/>
                        <line x1="12" x2="12.01" y1="16" y2="16"/>
                    </svg>
                    <span class="text-sm"><?php echo htmlspecialchars($error); ?></span>
                </div>
                <?php endif; ?>

                <?php if ($imageUrl && !$error): ?>
                <div class="flex flex-col items-center space-y-4">
                    <?php if ($bookTitle || $bookAuthor || $bookPublisher): ?>
                    <div class="text-center space-y-1">
                        <?php if ($bookTitle): ?>
                        <h3 class="font-semibold text-amber-900 text-lg"><?php echo htmlspecialchars($bookTitle); ?></h3>
                        <?php endif; ?>
                        <?php if ($bookAuthor): ?>
                        <p class="text-sm text-amber-700"><?php echo htmlspecialchars($bookAuthor); ?></p>
                        <?php endif; ?>
                        <?php if ($bookPublisher): ?>
                        <p class="text-xs text-amber-600">Yayinevi: <?php echo htmlspecialchars($bookPublisher); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <div class="bg-white p-4 rounded-lg shadow-inner border border-amber-100">
                        <img 
                            src="<?php echo htmlspecialchars($imageUrl); ?>" 
                            alt="<?php echo $bookTitle ? htmlspecialchars($bookTitle) : 'Kitap Kapağı'; ?>"
                            class="max-w-full h-auto max-h-80 object-contain rounded"
                            onerror="this.parentElement.innerHTML='<p class=\'text-red-600 text-sm p-4\'>Kitap kapağı bulunamadı</p>'"
                        >
                    </div>
                    <p class="text-xs text-amber-600 text-center">Görsel <?php echo htmlspecialchars($imageSource); ?>'dan alinmistir</p>
                </div>
                <?php elseif (empty($isbn)): ?>
                <div class="flex flex-col items-center justify-center py-8 text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                    </svg>
                    <p class="mt-2 text-sm text-amber-500">Kitap kapağını görmek için ISBN girin</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <p class="text-center text-xs text-amber-600 mt-4">
            Örnek: 9780141439518 (Pride and Prejudice)
        </p>
    </div>
</body>
</html>
