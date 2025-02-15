<?php
declare(strict_types=1);

namespace Data;

use PDO;
use Data\DBConfig;
use PDOException;

use Entities\Klant;

class KlantDAO
{
    public function getKlant(string $email): ?array
    {
        $dbh = new PDO(
            DBConfig::$DB_CONNSTRING,
            DBConfig::$DB_USERNAME,
            DBConfig::$DB_PASSWORD
        );

        $stmt = $dbh->prepare("SELECT k.*, s.Naam AS gemeente_naam, s.Postcode 
                                FROM klanten k
                                INNER JOIN steden s ON k.Plaats_ID = s.Plaats_ID
                                WHERE k.Email = :email");
        $stmt->bindValue(":email", $email);
        $stmt->execute();

        $resultSet = $stmt->fetch(PDO::FETCH_ASSOC);

        $dbh = null;

        if ($resultSet) {
            $speciale_bemerkingen = $resultSet["Speciale_bemerkingen"] !== null ? (string) $resultSet["Speciale_bemerkingen"] : '';
            $promotieprijs_toepasbaar = $resultSet["Promotieprijs_toepasbaar"] ? '1' : '0';

            $klant = new Klant(
                (int) $resultSet["Klant_ID"],
                $resultSet["Naam"],
                $resultSet["Voornaam"],
                $resultSet["Straat"],
                $resultSet["Huisnummer"],
                (int) $resultSet["Plaats_ID"],
                $resultSet["Telefoonnummer"],
                $resultSet["Email"],
                $resultSet["Wachtwoord"],
                $promotieprijs_toepasbaar,
                $speciale_bemerkingen,
                $resultSet["Laatste_aanmelding_email"]
            );

            return [
                'klant' => $klant,
                'gemeente_naam' => $resultSet["gemeente_naam"],
                'Postcode' => $resultSet["Postcode"]
            ];
        } else {
            return null;
        }
    }


    public function register(array $data): ?Klant
    {
        // Password hashing
        $hashedPassword = password_hash($data['wachtwoord'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO klanten (naam, voornaam, straat, huisnummer, plaats_ID, telefoonnummer, email, wachtwoord, speciale_bemerkingen, promotieprijs_toepasbaar, laatste_aanmelding_email) 
                VALUES (:naam, :voornaam, :straat, :huisnummer, :plaats_ID, :telefoonnummer, :email, :wachtwoord, :speciale_bemerkingen, :promotieprijs_toepasbaar, :laatste_aanmelding_email)";
        $dbh = new PDO(
            DBConfig::$DB_CONNSTRING,
            DBConfig::$DB_USERNAME,
            DBConfig::$DB_PASSWORD
        );

        $stmt = $dbh->prepare($sql);
        $stmt->execute([
            ':naam' => $data['naam'],
            ':voornaam' => $data['voornaam'],
            ':straat' => $data['straat'],
            ':huisnummer' => $data['huisnummer'],
            ':plaats_ID' => $data['plaats_ID'],
            ':telefoonnummer' => $data['telefoonnummer'],
            ':email' => $data['email'],
            ':wachtwoord' => $hashedPassword,
            ':speciale_bemerkingen' => $data['speciale_bemerkingen'] ?? null,
            ':promotieprijs_toepasbaar' => $data['promotieprijs_toepasbaar'] ? 1 : 0,
            ':laatste_aanmelding_email' => $data['laatste_aanmelding_email'] ?? null
        ]);

        $klantId = (int) $dbh->lastInsertId();
        $dbh = null;

        if ($klantId) {
            $promotieprijs_toepasbaar = $data["Promotieprijs_toepasbaar"] ? '1' : '0';

            return new Klant(
                $klantId,
                $data['naam'],
                $data['voornaam'],
                $data['straat'],
                $data['huisnummer'],
                $data['plaats_ID'],
                $data['telefoonnummer'],
                $data['email'],
                $hashedPassword,
                $data['speciale_bemerkingen'] ?? null,
                $promotieprijs_toepasbaar,
                $data['laatste_aanmelding_email'] ?? null
            );
        } else {
            return null;
        }
    }

    public function login(string $email, string $wachtwoord): ?Klant
    {
        $dbh = new PDO(
            DBConfig::$DB_CONNSTRING,
            DBConfig::$DB_USERNAME,
            DBConfig::$DB_PASSWORD
        );

        $stmt = $dbh->prepare("SELECT * FROM klanten WHERE email = :email");
        $stmt->bindValue(":email", $email);
        $stmt->execute();

        $resultSet = $stmt->fetch(PDO::FETCH_ASSOC);

        $dbh = null;

        if ($resultSet) {
            $speciale_bemerkingen = $resultSet["Speciale_bemerkingen"] !== '' ? (string) $resultSet["Speciale_bemerkingen"] : null;
            $promotieprijs_toepasbaar = $resultSet["Promotieprijs_toepasbaar"] ? '1' : '0';

            return new Klant(
                (int) $resultSet["Klant_ID"],
                $resultSet["Naam"],
                $resultSet["Voornaam"],
                $resultSet["Straat"],
                $resultSet["Huisnummer"],
                (int) $resultSet["Plaats_ID"],
                $resultSet["Telefoonnummer"],
                $resultSet["Email"],
                $resultSet["Wachtwoord"],
                $speciale_bemerkingen,
                $promotieprijs_toepasbaar,
                $resultSet["Laatste_aanmelding_email"]
            );
        } else {
            return null;
        }
    }

    public function getKlantByEmail($email): ?Klant
    {
        $dbh = new PDO(
            DBConfig::$DB_CONNSTRING,
            DBConfig::$DB_USERNAME,
            DBConfig::$DB_PASSWORD
        );

        $stmt = $dbh->prepare("SELECT * FROM klanten WHERE email = :email");
        $stmt->bindValue(":email", $email);
        $stmt->execute();

        $resultSet = $stmt->fetch(PDO::FETCH_ASSOC);

        $dbh = null;

        if ($resultSet) {
            $speciale_bemerkingen = $resultSet["Speciale_bemerkingen"] !== '' ? (string) $resultSet["Speciale_bemerkingen"] : null;
            $promotieprijs_toepasbaar = $resultSet["Promotieprijs_toepasbaar"] ? '1' : '0';

            return new Klant(
                (int) $resultSet["Klant_ID"],
                $resultSet["Naam"],
                $resultSet["Voornaam"],
                $resultSet["Straat"],
                $resultSet["Huisnummer"],
                (int) $resultSet["Plaats_ID"],
                $resultSet["Telefoonnummer"],
                $resultSet["Email"],
                $resultSet["Wachtwoord"],
                $speciale_bemerkingen,
                $promotieprijs_toepasbaar,
                $resultSet["Laatste_aanmelding_email"]
            );
        } else {
            return null;
        }
    }






    public function getKlantById(int $klant_ID): ?Klant
    {
        $dbh = new PDO(
            DBConfig::$DB_CONNSTRING,
            DBConfig::$DB_USERNAME,
            DBConfig::$DB_PASSWORD
        );

        $stmt = $dbh->prepare("SELECT * FROM klanten WHERE klant_ID = :klant_ID");
        $stmt->bindValue(":klant_ID", $klant_ID);
        $stmt->execute();

        $resultSet = $stmt->fetch(PDO::FETCH_ASSOC);

        $dbh = null;

        if ($resultSet) {
            return new Klant(
                (int) $resultSet["klant_ID"],
                $resultSet["naam"],
                $resultSet["voornaam"],
                $resultSet["straat"],
                $resultSet["huisnummer"],
                (int) $resultSet["plaats_id"],
                $resultSet["telefoonnummer"],
                $resultSet["email"],
                $resultSet["wachtwoord"],
                $resultSet["speciale_bemerkingen"],
                $resultSet["promotieprijs_toepasbaar"],
                $resultSet["laatste_aanmelding_email"]
            );
        } else {
            return null;
        }
    }



    public function updateKlant(Klant $klant): void
    {
        try {
            $dbh = new PDO(
                DBConfig::$DB_CONNSTRING,
                DBConfig::$DB_USERNAME,
                DBConfig::$DB_PASSWORD
            );
            $stmt = $dbh->prepare("UPDATE klanten SET 
                                        Straat = :straat,
                                        Huisnummer = :huisnummer,
                                        Plaats_ID = :plaats_id
                                        WHERE Klant_ID = :klant_id");

            $stmt->bindValue(":straat", $klant->getStraat());
            $stmt->bindValue(":huisnummer", $klant->getHuisnummer());
            $stmt->bindValue(":plaats_id", $klant->getPlaats_id(), PDO::PARAM_INT);
            $stmt->bindValue(":klant_id", $klant->getId(), PDO::PARAM_INT);

            $stmt->execute();
            $dbh = null;
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
