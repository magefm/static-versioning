<?php

class MageFM_StaticVersioning_Model_Core_Design_Package extends Mage_Core_Model_Design_Package
{

    public function getMergedCssUrl($files)
    {
        // secure or unsecure
        $isSecure = Mage::app()->getRequest()->isSecure();
        $mergerDir = $isSecure ? 'css_secure' : 'css';
        $targetDir = $this->_initMergerDir($mergerDir);
        if (!$targetDir) {
            return '';
        }

        // base hostname & port
        $baseMediaUrl = Mage::getBaseUrl('media', $isSecure);
        $hostname = parse_url($baseMediaUrl, PHP_URL_HOST);
        $port = parse_url($baseMediaUrl, PHP_URL_PORT);
        if (false === $port) {
            $port = $isSecure ? 443 : 80;
        }

        // merge into target file
        $targetFilename = md5(implode(',', $files) . "|{$hostname}|{$port}") . '.css';
        $mergeFilesResult = $this->_mergeFiles(
                $files, $targetDir . DS . $targetFilename, false, array($this, 'beforeMergeCss'), 'css'
        );
        if ($mergeFilesResult) {
            $filePath = $targetDir . DS . $targetFilename;
            return $baseMediaUrl . $mergerDir . '/' . $targetFilename . (file_exists($filePath) ? '?v=' . md5_file($filePath) : '');
        }
        return '';
    }
    
    public function getMergedJsUrl($files)
    {
        $targetFilename = md5(implode(',', $files)) . '.js';
        $targetDir = $this->_initMergerDir('js');
        if (!$targetDir) {
            return '';
        }
        if ($this->_mergeFiles($files, $targetDir . DS . $targetFilename, false, null, 'js')) {
            $filePath = $targetDir . DS . $targetFilename;
            return Mage::getBaseUrl('media', Mage::app()->getRequest()->isSecure()) . 'js/' . $targetFilename . (file_exists($filePath) ? '?v=' . md5_file($filePath) : '');
        }
        return '';
    }

}
