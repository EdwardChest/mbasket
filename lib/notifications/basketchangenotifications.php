<?php

namespace Sotbit\Multibasket\Notifications;

use Bitrix\Main\Session\SessionInterface;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Type\Contract\Arrayable;
use Sotbit\Multibasket\DTO\DtoTrait;

/**
 * @property null|RecolorBasket $order
 * @property null|RecolorBasket[] $changeColor
 * @property null|RecolorBasket $united
 * @property null|MoveProductsToBasket $moveProductToBasket
 */

class BasketChangeNotifications implements Arrayable
{
    use DtoTrait;

    const KEY = 'BASKET_CHANGE_NOTIFICATIONS';

    const FILD_TYPES = [
        'order' => 'object',
        'changeColor' => 'array',
        'united' => 'object',
        'moveProductToBasket' => 'object',
    ];

    protected $order;
    protected $changeColor;
    protected $united;
    protected $moveProductToBasket;

    public function __construct(array $requestData)
    {
        $this->construct($requestData, self::FILD_TYPES);
    }

    public function setToSession(SessionInterface $ssesion): void
    {
        $ssesion->set(self::KEY, Json::encode(self::getAsArray($this)));
    }

    public static function take(SessionInterface $ssesion): self
    {
        $instancesString = $ssesion->get(self::KEY);

        if (empty($instancesString)) {
            return new Self([]);
        } else {
            $ssesion->remove(self::KEY);
            $array = Json::decode($instancesString);

            $order = is_array($array['order']) ? new RecolorBasket($array['order']) :  new RecolorBasket([]);
            $united = is_array($array['united']) ? new RecolorBasket($array['united']) : new RecolorBasket([]);
            $changeColor = is_array($array['changeColor']) ? array_map(function ($i) {
                return is_array($i) ? new RecolorBasket($i) : new RecolorBasket([]);
            }, $array['changeColor']) : [];

            $moveProductToBasket = is_array($array['moveProductToBasket'])
                ? new MoveProductsToBasket($array['moveProductToBasket'])
                : new RecolorBasket([]);

            return new self(compact('order', 'changeColor', 'united', 'moveProductToBasket'));
        }
    }

    public function setCurrentBasketColor(string $color)
    {
        $this->united = new RecolorBasket([
            'fromColor' => $this->united->fromColor,
            'toColor' => $color,
        ]);
    }


}