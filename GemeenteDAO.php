<?php
declare(strict_types=1);

namespace Data;

use PDO;
use Data\DBConfig;
use Entities\Gemeente;

class GemeenteDAO
{
    public function getGemeenteByPostcode($postcode): ?Gemeente
    {
        $sql = "SELECT Plaats_ID, Postcode, Naam FROM steden WHERE Postcode = :postcode";
        $dbh = new PDO(
            DBConfig::$DB_CONNSTRING,
            DBConfig::$DB_USERNAME,
            DBConfig::$DB_PASSWORD
        );
        $stmt = $dbh->prepare($sql);
        $stmt->execute([':postcode' => $postcode]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $dbh = null;

        if ($result) {
            return new Gemeente(
                $result['Plaats_ID'],
                $result['Postcode'], 
                $result['Naam']
            );
        } else {
            return null;
        }
    }

    
}
