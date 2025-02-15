<?php
declare(strict_types=1);

namespace Data;

use PDO;
use PDOException;
use Entities\Bestelling;

class BestellingDAO
{
    public function createBestelling(Bestelling $bestelling): int
    {
        try {
            $dbh = new PDO(DBConfig::$DB_CONNSTRING, DBConfig::$DB_USERNAME, DBConfig::$DB_PASSWORD);
            $sql = "INSERT INTO bestellingen (Klant_ID, Datum, Tijdstip, Totaalprijs, Bezorgadres, Opmerkingen_koerier)
                    VALUES (:klant_id, :datum, :tijdstip, :totaalprijs, :bezorgadres, :opmerkingen_koerier)";
            $stmt = $dbh->prepare($sql);

            $stmt->bindValue(':klant_id', $bestelling->getKlant_ID());
            $stmt->bindValue(':datum', $bestelling->getDatum());
            $stmt->bindValue(':tijdstip', $bestelling->getTijdstip());
            $stmt->bindValue(':totaalprijs', $bestelling->getTotaalPrijs());
            $stmt->bindValue(':bezorgadres', $bestelling->getBezorgAdres());
            $stmt->bindValue(':opmerkingen_koerier', $bestelling->getOpmerkingKoerier());

            $stmt->execute();
            $lastInsertId = (int) $dbh->lastInsertId();

            $dbh = null;

            return $lastInsertId;
        } catch (PDOException $e) {
            throw $e;
        }
    }
}
