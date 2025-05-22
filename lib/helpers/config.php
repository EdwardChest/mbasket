<?php

namespace Sotbit\Multibasket\Helpers;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\OrderPropsTable;
use Bitrix\Sale\Internals\PersonTypeTable;
use Sotbit\Multibasket\Entity\MBasketTable;
use Sotbit\Multibasket\Models\MBasketCollection;
use Bitrix\Sale\Internals\FuserTable;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class Config
{
    const MODULE_ID = 'sotbit.multibasket';
    const PARAM_NAME = 'sotbit.multibasket_config';

    /**
     * All sites
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getSites(): array
    {
        $sites = [];
        $rs = \Bitrix\Main\SiteTable::getList([
            'select' => ['NAME', 'LID'],
            'filter' => ['ACTIVE' => 'Y'],
        ])->fetchAll();

        $sites = array_column($rs, 'NAME', 'LID');

        if (!is_array($sites) || count($sites) == 0) {
            echo "Cannot get sites";
        }

        return $sites;
    }

    public static function setDefault()
    {
        $default = serialize([
            'enableDeleteNotRegisterUsers' => true,
            'enableDeleteRegisterUsers' => false,
            'timeDeleteNotRegisterUsers' => 100,
            'timeDeleteRegisterUsers' => 100,
            'module_enabled' => false,
            'moduleWorkMode' => 'default',
        ]);
        Option::set(
            self::MODULE_ID,
            self::PARAM_NAME,
            $default,
            '',
        );
        self::removeAgent(false, 100, true);
        self::setAgent(false, 100, true);
    }

    public static function deletConfig(): void
    {
        Option::delete(
            self::MODULE_ID,
            ['name' => self::PARAM_NAME],
        );
    }

    public static function moduleIsEnabled($siteId): bool
    {
        return (bool)self::getConfig()[$siteId]['moduleEnabled'];
    }

    public static function getWorkMode($siteId): string
    {
        return self::getConfig()[$siteId]['moduleWorkMode'] ?: 'default';
    }

    public static function getConfig(): array
    {
        $sites = self::getSites();
        $siteConfig = [];
        foreach ($sites as $siteId => $name) {
            $result = Option::get(
                self::MODULE_ID,
                self::PARAM_NAME,
                "",
                $siteId,
            );
            $siteConfig[$siteId] = unserialize($result);
        }
        return $siteConfig;
    }

    public static function setSiteParam(HttpRequest $request, string $site, array $oldConfig): array
    {
        $postParam = $request->getPostList();
        $enableDeleteNotRegisterUsers = isset($postParam['enableDeleteNotRegisterUsers']) ? true : false;
        $enableDeleteRegisterUsers = isset($postParam['enableDeleteRegisterUsers']) ? true : false;
        $timeDeleteNotRegisterUsers = (int)$postParam['timeDeleteNotRegisterUsers'];
        $timeDeleteRegisterUsers = (int)$postParam['timeDeleteRegisterUsers'];
        $moduleEnabled = isset($postParam['moduleEnabled']) ? true : false;
        $moduleWorkMode = $postParam['moduleWorkMode'];
        $ratioBasketIBlock = $postParam['ratioBasketIBlock'] ? serialize($postParam['ratioBasketIBlock']) : false;
        $isSpecifyBasket = $postParam['isSpecifyBasket'];
        $specifyOrderProperty = $postParam['specifyOrderProperty'] ? serialize($postParam['specifyOrderProperty']) : false;

        $newParam = compact(
            'enableDeleteNotRegisterUsers',
            'enableDeleteRegisterUsers',
            'timeDeleteNotRegisterUsers',
            'timeDeleteRegisterUsers',
            'moduleEnabled',
            'moduleWorkMode',
            'ratioBasketIBlock',
            'isSpecifyBasket',
            'specifyOrderProperty',
        );
        Option::set(
            self::MODULE_ID,
            self::PARAM_NAME,
            serialize($newParam),
            $site,
        );

        if (!$ratioBasketIBlock) {
            Option::set(
                self::MODULE_ID,
                'STORE_COLOR',
                false,
                $site,
            );
        }

        $conditionNoUsers = $enableDeleteNotRegisterUsers !== $oldConfig['enableDeleteNotRegisterUsers']
            || $timeDeleteNotRegisterUsers !== $oldConfig['timeDeleteNotRegisterUsers'];

        $conditionUsers = $enableDeleteRegisterUsers !== $oldConfig['enableDeleteRegisterUsers']
            || $timeDeleteRegisterUsers !== $oldConfig['timeDeleteRegisterUsers'];

        if ($conditionNoUsers) {

            self::removeAgent(false, $oldConfig['timeDeleteNotRegisterUsers']);
            if ($enableDeleteNotRegisterUsers) {
                self::setAgent(false, $timeDeleteNotRegisterUsers);
            }
        }

        if ($conditionUsers) {

            self::removeAgent(true, $oldConfig['timeDeleteRegisterUsers']);
            if ($enableDeleteRegisterUsers) {
                self::setAgent(true, $timeDeleteRegisterUsers);
            }
        }

        return $newParam;
    }

    private static function setAgent(bool $resiterUser, int $time): void
    {
        \CAgent::AddAgent(
            self::deleteMultibaskets($resiterUser, $time, true),
            self::MODULE_ID,
            'Y',
            86400,
        );
    }

    private static function removeAgent(bool $resiterUser, int $time): void
    {
        \CAgent::RemoveAgent(
            self::deleteMultibaskets($resiterUser, $time, true),
            self::MODULE_ID,
        );
    }

    public static function deleteMultibaskets(bool $resiterUser, int $time, bool $install = false)
    {
        Loader::includeModule('sale');
        $start = time();

        $bool = $resiterUser ? 1 : 0;
        if ($install) {
            return __METHOD__ . "({$bool}, {$time});";
        }

        $timeSec = $time * 24 * 60 * 60;
        $removeData = DateTime::createFromTimestamp(time() - $timeSec);

        if ($resiterUser) {
            $fusers = FuserTable::query()
                ->setSelect(['ID'])
                ->whereNotNull('USER_ID')
                ->fetchAll();
        } else {
            $fusers = FuserTable::query()
                ->setSelect(['ID'])
                ->whereNull('USER_ID')
                ->fetchAll();
        }

        /** @var MBasketCollection */
        $basketCollection = MBasketTable::query()
            ->setSelect(['ID', 'ITEMS.ID', 'ITEMS.PROPS.ID'])
            ->where('DATE_REFRESH', '<', $removeData)
            ->whereIn('FUSER_ID', array_column($fusers, 'ID'))
            ->fetchCollection();

        foreach ($basketCollection as $basket) {
            foreach ($basket->getItems() as $item) {
                foreach ($item->getProps() as $prop) {
                    $prop->delete();
                    $end = time();
                    $res = $end - $start;
                }
                $item->delete();
                file_put_contents('agent_work_time', "\n time: $res");
            }
            $basket->delete();
        }

        return __METHOD__ . "({$bool}, {$time});";
    }

    public static function getModuleWorkMode()
    {
        return [
            'default' => Loc::getMessage('SOTBIT_MULTIBASKET_MODE_DEFAULT'),
            'store' => Loc::getMessage('SOTBIT_MULTIBASKET_MODE_STORE')
        ];
    }

    public static function getPropertyOrder()
    {
        $result = [];

        $arPerson = self::getPersonTypes();

        $dbOrderProps = OrderPropsTable::getList(['select' => ['NAME', 'PERSON_TYPE_ID', 'ID']]);
        while ($arOrderProps = $dbOrderProps->fetch()) {
            $result['REFERENCE_ID'][] = $arOrderProps['ID'];
            $result['REFERENCE'][] = $arOrderProps['NAME'] . ' [' . $arPerson[$arOrderProps['PERSON_TYPE_ID']] . ']';
        }

        return $result;
    }

    public static function getPersonTypes()
    {
        $result = [];

        $dbPersonType = PersonTypeTable::getList(['select' => ['NAME', 'ID']]);
        while ($arPersonType = $dbPersonType->fetch()) {
            $result[$arPersonType['ID']] = $arPersonType['NAME'];
        }

        return $result;
    }

    public static function getConfigPropertyOrder($siteId)
    {
        $result = [];

        if (self::getConfig()[$siteId]['isSpecifyBasket'] == 'on') {
            $result = unserialize(self::getConfig()[$siteId]['specifyOrderProperty']) ?: [];
        }

        return $result;
    }

    public static function getOption($name, $siteId='') {
        return Option::get(
            self::MODULE_ID,
            $name,
            '',
            $siteId
        );
    }
}