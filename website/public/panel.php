<?php
if (!defined('IN_INDEX')) {
    define('LOCATION', 'panel');
    include('index.php');
    exit;
}

if (!isset($_SESSION['id'])) {
    TwigHelper::addMsg('Dostęp tylko dla zalogowanych', 'error');
    header("Location: /");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['title'], $_POST['description'],
        $_POST['price'], $_POST['category_id']) && isset($_FILES['photo'])) {

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
                           "INSERT INTO Offers (UserID, title, description, category, photo_path, price, subwallet, status)
                            VALUES (:userid, :title, :description, :category, :photo, :price, :subwallet, :status)"
                        );

                        // $ltx_path = __DIR__ . '/../bin/ltx';
                        // $cmd = escapeshellcmd("$ltx_path --getnewaddress");
                        // // $ltx_raw_output = trim(shell_exec($cmd));
                        // echo shell_exec($cmd);
                        // echo "kys";
                        $ltx_get_new_address = shell_exec('../bin/ltx --getnewaddress 2>&1'); // redirecting err to stdout to see whats going on
                        echo "<pre>$ltx_get_new_address</pre>";

                        // Debug: Show the raw value to verify functionality
                        // Check if it looks like a valid tltc address
                        // if (preg_match('/^tltc[a-z0-9]{20,}$/i', $ltx_raw_output)) {
                        //     $newaddress = $ltx_raw_output;
                        // } else {
                        //     TwigHelper::addMsg('Błąd podczas generowania adresu przez ltx.', 'error');
                        //     header("Location: /panel");
                        //     exit;
                        // }

                        $stmt->execute([
                            ':userid'     => $_SESSION['id'],
                            ':title'      => $title,
                            ':description'=> $description,
                            ':category'   => $category,
                            ':photo'      => $new_filename, // Use the new, safe filename
                            ':price'      => $price,
                            ':subwallet'  => $ltx_get_new_address, // Placeholder
                            ':status'     => 'active'
                        ]);

                        TwigHelper::addMsg('Oferta została pomyślnie dodana!', 'success');

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

// Fetch the user's offers to display on the page
$stmt = DB::getInstance()->prepare("SELECT * FROM Offers WHERE UserID = :userid ORDER BY OfferID DESC");
$stmt->execute([':userid' => $_SESSION['id']]);
$user_offers = $stmt->fetchAll();

// Render the page with the offers data
print TwigHelper::getInstance()->render('panel.html', [
    'user_offers' => $user_offers
]);
?>
