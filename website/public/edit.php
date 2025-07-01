<?php
/*
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('IN_INDEX')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    include( 'config.php' );
    include( 'helpers.php' );
}

$id = $_GET['id'];
$userID = $_SESSION['id'] ?? null;

$stmt = DB::getInstance()->prepare("SELECT * FROM Offers WHERE OfferID = ?");
$stmt->execute([$id]);
$offer = $stmt->fetch();

if( $offer && $offer['UserID'] === $userID &&
    $offer['status'] === 'active' ){
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['title'], $_POST['description'],
            $_POST['price'], $_POST['category_id'])) {

            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $price = trim($_POST['price']);
            $category = trim($_POST['category_id']);
            $file = $_FILES['photo'];

            // --- File Upload Logic ---
            $upload_dir = 'uploads/';
            $allowed_types = ['image/png', 'image/jpeg', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5 MB

            // 2. Check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                TwigHelper::addMsg('Błąd podczas przesyłania pliku. Spróbuj ponownie.', 'error');
            }
            // 3. Check file size
            elseif ($file['size'] > $max_size) {
                TwigHelper::addMsg('Plik jest zbyt duży. Maksymalny rozmiar to 5MB.', 'error');
            }
            // 4. Check file MIME type for security
            else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mime_type, $allowed_types)) {
                    TwigHelper::addMsg('Niedozwolony typ pliku. Wybierz PNG, JPG lub WEBP.', 'error');
                } else {
                    // 5. Create a unique, safe filename
                    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $new_filename = uniqid('offer_', true) . '.' . strtolower($file_extension);
                    $destination = $upload_dir . $new_filename;

                    // 6. Move the file to the final destination
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        // SUCCESS! File is saved. Now insert into database.

                        try {
                            $stmt = DB::getInstance()->prepare(
                                "UPDATE Offers
                                SET title = :title,
                                description = :description,
                                category = :category,
                                photo_path = :photo,
                                price = :price
                                WHERE OfferID = :offerid"
                            );

                            $stmt->execute([
                                ':title'      => $title,
                                ':description'=> $description,
                                ':category'   => $category,
                                ':photo'      => $new_filename, // Use the new, safe filename
                                ':price'      => $price,
                                ':offerid'     => $id
                            ]);

                            TwigHelper::addMsg('Oferta została pomyślnie edytowana!', 'success');

                        } catch (PDOException $e) {
                            // // Handle potential database errors
                            // TwigHelper::addMsg('Błąd bazy danych: ' . $e->getMessage(), 'error');
                            // // Clean up by deleting the uploaded file if DB insert fails
                            // if (file_exists($destination)) {
                            //     unlink($destination);
                            // }
                            echo $e -> getMessage();
                        }

                    } else {
                        TwigHelper::addMsg('Nie udało się zapisać pliku na serwerze.', 'error');
                    }
                }
            }
        } else {
            TwigHelper::addMsg('Wypełnij wszystkie pola formularza.', 'error');
        }

        // 7. Redirect back to the panel to prevent form re-submission
        header("Location: /panel");
        exit;
    }
    print TwigHelper::getInstance()->render('edit.html', [
        'offer' => $offer
    ]);
} else {
    print TwigHelper::getInstance()->render('error.html', []);

}*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('IN_INDEX')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    include('config.php');
    include('helpers.php');
}

$id = $_GET['id'] ?? null;
$userID = $_SESSION['id'] ?? null;

$stmt = DB::getInstance()->prepare("SELECT * FROM Offers WHERE OfferID = ?");
$stmt->execute([$id]);
$offer = $stmt->fetch();

if ($offer && $offer['UserID'] === $userID && $offer['status'] === 'active') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST['title'], $_POST['description'], $_POST['price'], $_POST['category_id'])) {

            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $price = trim($_POST['price']);
            $category = trim($_POST['category_id']);
            $photo_path = $offer['photo_path'];  // domyślnie stare zdjęcie

            $file = $_FILES['photo'] ?? null;

            if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {

                // --- Upload logic ---
                $upload_dir = 'uploads/';
                $allowed_types = ['image/png', 'image/jpeg', 'image/webp'];
                $max_size = 5 * 1024 * 1024; // 5 MB

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    TwigHelper::addMsg('Błąd podczas przesyłania pliku. Spróbuj ponownie.', 'error');
                } elseif ($file['size'] > $max_size) {
                    TwigHelper::addMsg('Plik jest zbyt duży. Maksymalny rozmiar to 5MB.', 'error');
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);

                    if (!in_array($mime_type, $allowed_types)) {
                        TwigHelper::addMsg('Niedozwolony typ pliku. Wybierz PNG, JPG lub WEBP.', 'error');
                    } else {
                        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $new_filename = uniqid('offer_', true) . '.' . strtolower($file_extension);
                        $destination = $upload_dir . $new_filename;

                        if (move_uploaded_file($file['tmp_name'], $destination)) {
                            $photo_path = $new_filename;

                            // (Opcjonalnie) usuń stare zdjęcie:
                            // if (!empty($offer['photo_path']) && file_exists($upload_dir . $offer['photo_path'])) {
                            //     unlink($upload_dir . $offer['photo_path']);
                            // }

                        } else {
                            TwigHelper::addMsg('Nie udało się zapisać pliku na serwerze.', 'error');
                        }
                    }
                }
            }

            // --- Aktualizacja oferty ---
            try {
                $stmt = DB::getInstance()->prepare(
                    "UPDATE Offers
                     SET title = :title,
                         description = :description,
                         category = :category,
                         photo_path = :photo,
                         price = :price
                     WHERE OfferID = :offerid"
                );

                $stmt->execute([
                    ':title'       => $title,
                    ':description' => $description,
                    ':category'    => $category,
                    ':photo'       => $photo_path,
                    ':price'       => $price,
                    ':offerid'     => $id
                ]);

                TwigHelper::addMsg('Oferta została pomyślnie edytowana!', 'success');

            } catch (PDOException $e) {
                TwigHelper::addMsg('Błąd bazy danych: ' . $e->getMessage(), 'error');
            }

        } else {
            TwigHelper::addMsg('Wypełnij wszystkie pola formularza.', 'error');
        }

        header("Location: /panel");
        exit;
    }

    print TwigHelper::getInstance()->render('edit.html', [
        'offer' => $offer
    ]);

} else {
    print TwigHelper::getInstance()->render('error.html', []);
}
?>
