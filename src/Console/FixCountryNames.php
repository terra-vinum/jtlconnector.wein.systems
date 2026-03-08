<?php

namespace Jtl\Connector\Vivino\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Jtl\Connector\Vivino\Application;
use Jtl\Connector\Core\Utilities\Str;

class FixCountryNames extends Command {

    protected static $defaultName = 'countries:names:fix';

    protected function execute(InputInterface $input, OutputInterface $output): int {

        $pdo = Application::pdo();

        $stmt = $pdo->prepare('SELECT * FROM country_names');
        $upd  = $pdo->prepare('UPDATE country_names SET country_name_en = ? WHERE id = ?');
        $stmt->execute();
        while ( $row = $stmt->fetch(\PDO::FETCH_OBJ) ) {
            $upd->execute([ ucwords(strtolower($row->country_name_en)),$row->id ]);
        }
        return 0;
    }
}
