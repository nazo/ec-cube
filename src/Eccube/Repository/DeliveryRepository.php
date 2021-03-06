<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Eccube\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * DelivRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DeliveryRepository extends EntityRepository
{

    public function findOrCreate($id)
    {
        if ($id == 0) {
            $em = $this->getEntityManager();

            $ProductType = $em
                ->getRepository('\Eccube\Entity\Master\ProductType')
                ->findOneBy(array(), array('rank' => 'DESC'));

            $Delivery = $this->findOneBy(array(), array('rank' => 'DESC'));

            $rank = 1;
            if ($Delivery) {
                $rank = $Delivery->getRank() + 1;
            }

            $Delivery = new \Eccube\Entity\Delivery();
            $Delivery
                ->setRank($rank)
                ->setDelFlg(0)
                ->setProductType($ProductType);
        } else {
            $Delivery = $this->find($id);
        }

        return $Delivery;
    }

    /**
     * 複数の商品種別から配送業者を取得
     *
     * @param $productTypes
     * @return array
     */
    public function getDeliveries($productTypes)
    {

        $deliveries = $this->createQueryBuilder('d')
            ->where('d.ProductType in (:productTypes)')
            ->setParameter('productTypes', $productTypes)
            ->getQuery()
            ->getResult();

        return $deliveries;
    }

    /**
     * 選択可能な配送業者を取得
     *
     * @param $payments
     * @return array
     */
    public function findAllowedDeliveries($productTypes, $payments)
    {

        $d = $this->getDeliveries($productTypes);
        $arr = array();

        foreach ($d as $Delivery) {
            $paymentOptions = $Delivery->getPaymentOptions();

            foreach ($paymentOptions as $PaymentOption) {
                foreach ($payments as $Payment) {
                    if ($PaymentOption->getPayment()->getId() == $Payment['id']) {
                        $arr[$Delivery->getId()] = $Delivery;
                        break;
                    }
                }
            }
        }

        return array_values($arr);
    }
}
