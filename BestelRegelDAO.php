<?php
declare(strict_types=1);

namespace Data;

use PDO;
use PDOException;
use Entities\Bestelregel;

class BestelregelDAO
{
    public function createBestelregel(Bestelregel $bestelregel): Bestelregel
    {
        try {
            $dbh = new PDO(DBConfig::$DB_CONNSTRING, DBConfig::$DB_USERNAME, DBConfig::$DB_PASSWORD);
            $sql = "INSERT INTO bestelregels (Bestelling_ID, Product_ID, Aantal, Prijs_per_stuk, Totaal_prijs)
                    VALUES (:bestelling_id, :product_id, :aantal, :prijs_per_stuk, :totaal_prijs)";
            $stmt = $dbh->prepare($sql);

            $stmt->bindValue(':bestelling_id', $bestelregel->getBestelling_ID());
            $stmt->bindValue(':product_id', $bestelregel->getProduct_ID());
            $stmt->bindValue(':aantal', $bestelregel->getAantal());
            $stmt->bindValue(':prijs_per_stuk', $bestelregel->getPrijsPerStuk());
            $stmt->bindValue(':totaal_prijs', $bestelregel->getTotaalPrijs());

            $stmt->execute();

            return $bestelregel;

        } catch (PDOException $e) {
            throw $e;
        } finally {
            $dbh = null;
        }
    }
}
