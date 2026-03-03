<?php

namespace Jtl\Connector\Vivino\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Jtl\Connector\Vivino\Application;
use Jtl\Connector\Core\Utilities\Str;

class WeinsysSyncAttributes extends Command {

    protected static $defaultName = 'weinsys:sync:attributes';

    protected function execute(InputInterface $input, OutputInterface $output): int {

        $context = stream_context_create([
            'http' => [
                'header' => 'x-weinsys-key: '.getenv('WEINSYS_KEY'),
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]);
        $json = file_get_contents(getenv('WEINSYS_URL').'/info/attributes',false,$context);
        $pdo = Application::pdo();


        $pdo->query('TRUNCATE TABLE weinsys_attributes');
        $stmt = $pdo->prepare('INSERT INTO weinsys_attributes (`name`,`label`) VALUES (?,?)');
        foreach (json_decode($json) as $attr ) {
            $stmt->execute([ $attr->name, $attr->label ]);
        }
        return 0;
    }
}
