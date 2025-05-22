<?
//1.1.10
if (IsModuleInstalled('sotbit.multibasket')) {
    //this code need in all future updates
    $rs = \Bitrix\Main\SiteTable::getList([
        'select' => ['SITE_NAME', 'LID', 'DIR'],
        'filter' => ['ACTIVE' => 'Y'],
    ])->fetchAll();

    foreach ($rs as $site) {
        if (is_dir($_SERVER['DOCUMENT_ROOT'] . $site['DIR'] . 'local/components/sotbit/multibasket.multibasket/')) {
            $updater->CopyFiles("install/components", $_SERVER['DOCUMENT_ROOT'] . $site['DIR'] . '/local/components');
        }
    }
}
?>