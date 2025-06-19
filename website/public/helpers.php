<?php

if (!defined('IN_INDEX')) { exit("This file can only be included in index.php."); }

// returns addres of current domain and saves to $_SERVER['HTTP_HOST']
function get_domain(): string {
    // preg_replace because we don't want someone to try injecting malicious script
    $domena = preg_replace('/[^a-zA-Z0-9\.]/', '', $_SERVER['HTTP_HOST']);
    return $domena;
}

// Help communicate with database
class DB {
    private static $dbh = null; // tutaj będziemy przechowywać obiekt PDO

    public static function getInstance(): PDO {
        // If there's no connection we make it
        if (!self::$dbh) {
            try {
                // Create new PDO object of connection with DB
                self::$dbh = new PDO("sqlite:" . CONFIG['db_path']);
                self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                exit("Cannot connect to the database: " . $e->getMessage());
            }
        }
        // Return PDO object ( connection with DB )
        return self::$dbh;
    }
}

// Creates Twig object at first getInstance().
// Additionally helps automate showing messages in templates
class TwigHelper {
    // Place for Twig object
    private static $twig = null;
    // Place for messages to show in templates
    private static $msg = [];

    public static function getInstance(): \Twig\Environment {
        // If we don't have Twig object we make it
        if (!self::$twig) {
            // Twig innitialization
            $twig_loader = new \Twig\Loader\FilesystemLoader('templates');
            self::$twig = new \Twig\Environment($twig_loader);
        }
        // Return Twig object
        return self::$twig;
    }

    // Ability to add messages of different types: success, info, error
    public static function addMsg(string $text, string $type): void {
        self::$msg[] = [
            'text' => $text,
            'type' => $type
        ];
    }

    // returns all messages
    public static function getMsg(): array {
        return self::$msg;
    }
}

// Adding some global variables, functions and methods
TwigHelper::getInstance()->addGlobal('CONFIG', CONFIG);
TwigHelper::getInstance()->addFunction(new \Twig\TwigFunction('get_domain', 'get_domain'));
TwigHelper::getInstance()->addFunction(new \Twig\TwigFunction('get_msg', 'TwigHelper::getMsg'));
TwigHelper::getInstance()->addGlobal('_session', $_SESSION);
TwigHelper::getInstance()->addGlobal('_post', $_POST);
