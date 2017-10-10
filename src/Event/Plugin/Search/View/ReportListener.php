<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace CsvMigrations\Event\Plugin\Search\View;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use CsvMigrations\MigrationTrait;

/**
 * @todo This Listener needs to be moved to the Application level to remove plugins coupling
 */
class ReportListener implements EventListenerInterface
{
    use MigrationTrait;

    /**
     * Implemented Events
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Search.Report.getReports' => 'getAllReports'
        ];
    }

    /**
     * getAllReports method
     *
     * @param Cake\Event\Event $event used for getting reports
     *
     * @return array $data with all .ini reports
     */
    public function getAllReports(Event $event)
    {
        $data = $this->getReports();

        return $data;
    }
}
