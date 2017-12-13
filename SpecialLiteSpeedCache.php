<?php

/**
 * 
 * LiteSpeedCache Configuration Page, can be viewed using: Special:LiteSpeedCache
 *
 * @since      0.1
 * @author     LiteSpeed Technologies <info@litespeedtech.com>
 * @copyright  Copyright (c) 2016-2017 LiteSpeed Technologies, Inc. (https://www.litespeedtech.com)
 * @license    https://opensource.org/licenses/GPL-3.0
 */
class SpecialLiteSpeedCache extends SpecialPage
{

    public function __construct()
    {
        parent::__construct('LiteSpeedCache', '', true);
    }

    /**
     * Main execution function
     * @since    0.1
     * @param    $par array Parameters passed to the page
     */
    public function execute($par)
    {
        LiteSpeedCache::log(__METHOD__);
        $this->setHeaders();
        $output = $this->getOutput();
        $output->setPageTitle($this->msg('litespeedcache_title'));
        $output->addWikiMsg('litespeedcache_desc');

        if (!$this->isSysAdmin()) {
            $this->showView();
        } else if ($par == 'edit') {
             if (count($_POST) == 0) {
                $this->showForm();
            } else if (isset($_POST['save'])) {
                $request = $this->getRequest();
                $config = array(
                    'lscache_enabled' => ($request->getText('lscacheEnabled') == "on"),
                    'login_user_cachable' => ($request->getText('loginUserCachable') == "on"),
                    'logging_enabled' => ($request->getText('loggingEnabled') == "on"),
                    'public_cache_timeout' => $request->getText('publicCacheTimeout'),
                    'private_cache_timeout' => $request->getText('privateCacheTimeout'),
                );

                LiteSpeedCache::saveLiteSpeedSetting($config, $this->getUser(), $this->getPageTitle());
                $this->showView();
            } else if (isset($_POST['purge'])) {
                LiteSpeedCache::purgeAll($this->getUser(), $this->getPageTitle()); 
                $this->showView();
            } else if (isset($_POST['restore'])) {
                LiteSpeedCache::restoreLiteSpeedSetting($this->getUser(), $this->getPageTitle());
                $this->showForm();
            } else if (isset($_POST['clear'])) {
                LiteSpeedCache::clearLiteSpeedLogging();
                $this->showView();
            }
        } else {
            $this->showView(false);
        }
        $output->addHTML('<p><br/><br/><a  href="https://www.litespeedtech.com"><img src="http://www.litespeedtech.com/images/logos/litespeed/LiteSpeed_Logo.svg" alt="LiteSpeed" width="100" height="auto">&nbsp;&nbsp;</a>Powered by <a href="https://www.litespeedtech.com/solutions">LiteSpeed LSCache</a> solution.</p>');
    }

    /**
     * Show a configuration Form for administrators.
     * @since    0.1
     */
    private function showForm()
    {
        LiteSpeedCache::log(__METHOD__);
        $this->setHeaders();
        $output = $this->getOutput();
        $config = LiteSpeedCache::getLiteSpeedSettig();

        $output->addHTML('<form action="" method="post">');
        $output->addHtml('<br/><table id="mw-htmlform-info">');
        $html = '<tr class="mw-htmlform-field-HTMLInfoField"><td class="mw-label"><label for="lscacheEnabled">' . $this->msg('litespeedcache_lscache_enabled') . '</label></td>';
        $output->addHTML($html);
        $html = '<td class="mw-input"><input type="checkbox" id="lscacheEnabled" name="lscacheEnabled" ' . $this->check($config['lscache_enabled']) . '></td></tr>';
        $output->addHTML($html);

        $html = '<tr class="mw-htmlform-field-HTMLInfoField"><td class="mw-label"><label for="publicCacheTimeout">' . $this->msg('litespeedcache_public_cache_timeout') . '</label></td>';
        $output->addHTML($html);
        $html = '<td class="mw-input"><input type="text" id="publicCacheTimeout" name="publicCacheTimeout" value="' . $config['public_cache_timeout'] . '"></td></tr>';
        $output->addHTML($html);

        $html = '<tr class="mw-htmlform-field-HTMLInfoField"><td class="mw-label"><label for="loginUserCachable">' . $this->msg('litespeedcache_login_user_cachable') . '</label></td>';
        $output->addHTML($html);
        $html = '<td class="mw-input"><input type="checkbox" id="loginUserCachable" name="loginUserCachable" ' . $this->check($config['login_user_cachable']) . '></td></tr>';
        $output->addHTML($html);

        $html = '<tr class="mw-htmlform-field-HTMLInfoField"><td class="mw-label"><label for="privateCacheTimeout">' . $this->msg('litespeedcache_private_cache_timeout') . '</label></td>';
        $output->addHTML($html);
        $html = '<td class="mw-input"><input type="text" id="privateCacheTimeout" name="privateCacheTimeout" value="' . $config['private_cache_timeout'] . '"></td></tr>';
        $output->addHTML($html);

        $html = '<tr class="mw-htmlform-field-HTMLInfoField"><td class="mw-label"><label for="loggingEnabled">' . $this->msg('litespeedcache_logging_enabled') . '</label></td>';
        $output->addHTML($html);
        $html = '<td class="mw-input"><input type="checkbox" id="loggingEnabled" name="loggingEnabled" ' . $this->check($config['logging_enabled']) . '></td></tr>';
        $output->addHTML($html);


        $html = '<tr class="mw-htmlform-field-HTMLInfoField"><td class="mw-label"><br/><button type = "submit" name="restore">' . $this->msg('litespeedcache_restore') . '</button>&nbsp;<button type = "submit" name="save">' . $this->msg('litespeedcache_save') . '</button></td>';
        $output->addHTML($html);
        $html = '<td class="mw-input"><br/><button type = "submit" name="purge">' . $this->msg('litespeedcache_purge') . '</button>&nbsp;<button type = "submit" name="clear">' . $this->msg('litespeedcache_clear') . '</button></td></tr> ';
        $output->addHTML($html);
        $output->addHTML('</table><br/></form>');
        $output->addWikiMsg('litespeedcache_beta');
    }

    /**
     * Show LiteSpeedCache configuration for all users.
     * 
     * @since    0.1
     */
    private function showView($fromEdit=true)
    {
        LiteSpeedCache::log(__METHOD__);
        $config = LiteSpeedCache::getLiteSpeedSettig();
        $output = $this->getOutput();
        $wikitext = $this->msg('litespeedcache_lscache_enabled') . $this->enabled($config['lscache_enabled']);
        $output->addWikiText('<br/>' . $wikitext);
        $wikitext = $this->msg('litespeedcache_public_cache_timeout') . $config['public_cache_timeout'];
        $output->addWikiText($wikitext);
        $wikitext = $this->msg('litespeedcache_login_user_cachable') . $this->enabled($config['login_user_cachable']);
        $output->addWikiText($wikitext);
        $wikitext = $this->msg('litespeedcache_private_cache_timeout') . $config['private_cache_timeout'];
        $output->addWikiText($wikitext);
        $wikitext = $this->msg('litespeedcache_logging_enabled') . $this->enabled($config['logging_enabled']);
        $output->addWikiText($wikitext);
        if ($this->isSysAdmin()) {
            if($fromEdit){
                $output->addHTML('<a href="./edit">' . $this->msg( 'litespeedcache_change_setting') . '</a>');
                $output->addHTML('&nbsp;&nbsp;<a href="../Special:Log/litespeedcache" target="_blank">' . $this->msg('litespeedcache_show_logs') . '</a>');
            }
            else{
                $output->addHTML('<a href="./Special:LiteSpeedCache/edit">' . $this->msg('litespeedcache_change_setting') . '</a>');
                $output->addHTML('&nbsp;&nbsp;<a href="./Special:Log/litespeedcache" target="_blank">' . $this->msg('litespeedcache_show_logs') . '</a>');
            }
        }
    }

    /**
     * Check if this page was viewed by an administrator.
     * 
     * @since    0.1
     */
    private function isSysAdmin()
    {
        if (!$this->getUser()) {
            return false;
        }
        $groups = $this->getUser()->getGroups();
        return array_search("sysop", $groups);
    }

    private function check($val)
    {
        if ($val) {
            return "checked";
        } else {
            return "";
        }
    }

    private function enabled($val)
    {
        if ($val) {
            return $this->msg('litespeedcache_enabled');
        } else {
            return $this->msg('litespeedcache_disabled');
        }
    }

    protected function getGroupName()
    {
        return 'wiki';
    }

}
