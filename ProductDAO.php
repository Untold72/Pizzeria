<?php
declare(strict_types=1);

namespace Data;


use \PDO;
use Data\DBConfig;
use Exception;
use Entities\Product;

class ProductDAO
{
    public function updateProductAvailability(): void
    {
        $currentDate = date('Y-m-d');

        try {
            $dbh = new PDO(
                DBConfig::$DB_CONNSTRING,
                DBConfig::$DB_USERNAME,
                DBConfig::$DB_PASSWORD
            );

            // Controleren of de functie al is uitgevoerd voor vandaag
            $queryCheckLastRun = "
                SELECT LaatstUitgevoerdOp
                FROM update_log
                WHERE Datum = :currentDate
            ";
            $stmtCheckLastRun = $dbh->prepare($queryCheckLastRun);
            $stmtCheckLastRun->bindParam(':currentDate', $currentDate);
            $stmtCheckLastRun->execute();
            $lastRun = $stmtCheckLastRun->fetch(PDO::FETCH_ASSOC);

            if ($lastRun) {
                // Functie is al uitgevoerd voor vandaag, dus stop hier
                return;
            }

            $dbh->beginTransaction();

            // Eerst alle producten op 0 zetten
            $queryReset = "
                UPDATE producten
                SET Beschikbaarheid = 0
            ";
            $stmtReset = $dbh->prepare($queryReset);
            $stmtReset->execute();

            // controleren welke producten beschikbaar moeten zijn
            $queryUpdate = "
                UPDATE producten p
                JOIN producten_seizoenen ps ON p.Product_ID = ps.Product_ID
                JOIN seizoenen s ON ps.Seizoen_ID = s.Seizoen_ID
                SET p.Beschikbaarheid = 1
                WHERE :currentDate BETWEEN s.Startdatum AND s.Einddatum
            ";
            $stmtUpdate = $dbh->prepare($queryUpdate);
            $stmtUpdate->bindParam(':currentDate', $currentDate);
            $stmtUpdate->execute();

            // Bijwerken van de log
            $queryUpdateLog = "
                INSERT INTO update_log (Datum, LaatstUitgevoerdOp)
                VALUES (:currentDate, NOW())
                ON DUPLICATE KEY UPDATE LaatstUitgevoerdOp = NOW()
            ";
            $stmtUpdateLog = $dbh->prepare($queryUpdateLog);
            $stmtUpdateLog->bindParam(':currentDate', $currentDate);
            $stmtUpdateLog->execute();

            $dbh->commit();

        } catch (Exception $e) {
            $dbh->rollBack();
            throw $e;
        } finally {
            $dbh = null;
        }
    }


    private function productToArray(Product $product): array
    {
        return [
            'product_ID' => $product->getProduct_ID(),
            'naam' => $product->getNaam(),
            'prijs' => $product->getPrijs(),
            'promotiePrijs' => $product->getPromotiePrijs(),
            'beschikbaarheid' => $product->getBeschikbaarheid(),
            'samenstelling' => $product->getSamenstelling(),
        ];
    }

    public function getAvailableProducts(): array
    {
        $dbh = new PDO(
            DBConfig::$DB_CONNSTRING,
            DBConfig::$DB_USERNAME,
            DBConfig::$DB_PASSWORD
        );
        $query = "SELECT * FROM producten WHERE Beschikbaarheid = 1 AND Samenstelling != ''";
        $stmt = $dbh->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];
        foreach ($result as $row) {
            $product = new Product(
                (int) $row['Product_ID'],
                $row['Naam'],
                (float) $row['Prijs'],            // Converteer naar float
                (float) $row['Promotieprijs'],    // Converteer naar float
                (bool) $row['Beschikbaarheid'],
                $row['Samenstelling']
            );

            $products[] = $this->productToArray($product);
        }

        $dbh = null;

        return $products;
    }


    private function ingredientToArray(Product $product): array
    {
        return [
            'product_ID' => $product->getProduct_ID(),
            'naam' => $product->getNaam(),
            'prijs' => $product->getPrijs(),
            'promotiePrijs' => $product->getPromotiePrijs(),
            'beschikbaarheid' => $product->getBeschikbaarheid(),
            'samenstelling' => $product->getSamenstelling(),
        ];
    }

    public function getIngredients(): array
    {
        $dbh = new PDO(
            DBConfig::$DB_CONNSTRING,
            DBConfig::$DB_USERNAME,
            DBConfig::$DB_PASSWORD
        );
        $query = "SELECT * FROM producten WHERE Beschikbaarheid = 1 AND Samenstelling = ''";
        $stmt = $dbh->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $products = [];
        foreach ($result as $row) {
            $product = new Product(
                (int) $row['Product_ID'],
                $row['Naam'],
                (float) $row['Prijs'],            // Converteer naar float
                (float) $row['Promotieprijs'],    // Converteer naar float
                (bool) $row['Beschikbaarheid'],
                $row['Samenstelling']
            );
    
            $products[] = $this->ingredientToArray($product);
        }
    
        $dbh = null;
    
        return $products;
    }
    
}
