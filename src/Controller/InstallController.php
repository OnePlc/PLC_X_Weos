<?php
/**
 * WeosController.php - Main Controller
 *
 * Main Controller Weos Module
 *
 * @category Controller
 * @package Weos
 * @author Verein onePlace
 * @copyright (C) 2020  Verein onePlace <admin@1plc.ch>
 * @license https://opensource.org/licenses/BSD-3-Clause
 * @version 1.0.0
 * @since 1.0.0
 */

declare(strict_types=1);

namespace OnePlace\Weos\Controller;

use Application\Controller\CoreUpdateController;
use Application\Model\CoreEntityModel;
use OnePlace\Weos\Model\WeosTable;
use Laminas\View\Model\ViewModel;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\ResultSet\ResultSet;

class InstallController extends CoreUpdateController {
    /**
     * WeosController constructor.
     *
     * @param AdapterInterface $oDbAdapter
     * @param WeosTable $oTableGateway
     * @since 1.0.0
     */
    public function __construct(AdapterInterface $oDbAdapter, $oTableGateway, $oServiceManager)
    {
        $this->oTableGateway = $oTableGateway;
        $this->sSingleForm = 'weos-single';
        parent::__construct($oDbAdapter, $oTableGateway, $oServiceManager);
    }

    public function checkdbAction()
    {
        # Set Layout based on users theme
        $this->setThemeBasedLayout('weos');

        $oRequest = $this->getRequest();

        if(! $oRequest->isPost()) {

            $bTableExists = false;

            try {
                //$this->oTableGateway->select();
                //$bTableExists = true;
            } catch (\RuntimeException $e) {

            }

            return new ViewModel([
                'bTableExists' => $bTableExists,
                'sVendor' => 'oneplace',
                'sModule' => 'oneplace-weos',
            ]);
        } else {
            $sSetupConfig = $oRequest->getPost('plc_module_setup_config');

            $sSetupFile = 'vendor/oneplace/oneplace-weos/data/install.sql';
            if(file_exists($sSetupFile)) {
                echo 'got install file..';
                $this->parseSQLInstallFile($sSetupFile,CoreUpdateController::$oDbAdapter);
            }

            if($sSetupConfig != '') {
                $sConfigStruct = 'vendor/oneplace/oneplace-weos/data/structure_'.$sSetupConfig.'.sql';
                if(file_exists($sConfigStruct)) {
                    echo 'got struct file for config '.$sSetupConfig;
                    $this->parseSQLInstallFile($sConfigStruct,CoreUpdateController::$oDbAdapter);
                }
                $sConfigData = 'vendor/oneplace/oneplace-weos/data/data_'.$sSetupConfig.'.sql';
                if(file_exists($sConfigData)) {
                    echo 'got data file for config '.$sSetupConfig;
                    $this->parseSQLInstallFile($sConfigData,CoreUpdateController::$oDbAdapter);
                }
                $sCustomLangFile = 'vendor/oneplace/oneplace-weos/data/de_DE_'.$sSetupConfig.'.po';
                if(file_exists($sCustomLangFile)) {
                    echo 'got lang file for config '.$sSetupConfig;
                    copy($sCustomLangFile,'vendor/oneplace/oneplace-weos/language/de_DE.po');
                    $sCustomLangFile = 'vendor/oneplace/oneplace-weos/data/de_DE_'.$sSetupConfig.'.mo';
                    copy($sCustomLangFile,'vendor/oneplace/oneplace-weos/language/de_DE.mo');
                }
            }

            symlink($_SERVER['DOCUMENT_ROOT'].'../vendor/oneplace/oneplace-event/public', $_SERVER['DOCUMENT_ROOT'].'vendor/oneplace-event');

            $oModTbl = new TableGateway('core_module', CoreUpdateController::$oDbAdapter);
            $oModTbl->insert([
                'module_key'=>'oneplace-weos',
                'type'=>'module',
                'version'=>\OnePlace\Weos\Module::VERSION,
                'label'=>'onePlace Weos',
                'vendor'=>'oneplace',
            ]);

            try {
                $this->oTableGateway->select();
                $bTableExists = true;
            } catch (\RuntimeException $e) {

            }
            $bTableExists = false;

            $this->flashMessenger()->addSuccessMessage('Weos DB Update successful');
            $this->redirect()->toRoute('application', ['action' => 'checkforupdates']);
        }
    }
}