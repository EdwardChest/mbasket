<?
$local = '/local/modules/sotbit.multibasket/admin/sotbit.multibasket.php';
$bitrix = '/bitrix/modules/sotbit.multibasket/admin/sotbit.multibasket.php';

if (file_exists($_SERVER["DOCUMENT_ROOT"] . $local)) {
    require($_SERVER["DOCUMENT_ROOT"] . $local);
} else {
    require($_SERVER["DOCUMENT_ROOT"] . $bitrix);
}
?>