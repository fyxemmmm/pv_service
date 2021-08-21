<?php

namespace api\controllers;

use common\models\Common;
use common\models\GoodsCartModel;
use common\models\GoodsModel;
use common\models\GoodsSpecificationModel;
use common\models\JushengVipModel;

class CartController extends CommonController
{

    public $modelClass = 'api\models\ArticleCollect';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['update'], $actions['create'], $actions['delete']);
        return $actions;
    }

    public function actionIndex()
    {
        $GoodsCartModel = GoodsCartModel::findOne(['u_id' => $this->userId]);
        $data = $GoodsCartModel ? $GoodsCartModel->data : [];

        $can_discount = Common::checkShopDiscount($this->userId, null);

        foreach ($data as $key => $value) {
            $g_id = $value['g_id'];
            $p_id = $value['p_id'];

            $GoodsModel = GoodsModel::findOne($g_id);

            if (!$GoodsModel) {
                unset($data[$key]);
                continue;
            }
            
            $name = $GoodsModel->name;
            $status = $GoodsModel->status;
            
            $GoodsSpecificationModel = GoodsSpecificationModel::findOne($p_id);

            if (!$GoodsSpecificationModel) {
                unset($data[$key]);
                continue;
            }

            $pdt_name = $GoodsSpecificationModel->name;
            $pdt_image_url = $GoodsSpecificationModel->image_url;
            if ($can_discount) {
                $price = $GoodsSpecificationModel->after_discount_cost;
            } else {
                $price = $GoodsSpecificationModel->original_cost;
            }
            
            $value['name'] = $name;
            $value['pdt_name'] = $pdt_name;
            $value['price'] = (float) $price;
            $value['total_price'] = (float) $price * $value['num'];
            $value['image_url'] = $pdt_image_url;
            $value['status'] = $status;

            $data[$key] = $value;
        }

        $times = array_column($data, 'time');
        array_multisort($times, SORT_DESC, $data);

        $group_carts = [
            'valid' => [],
            'invalid' => []
        ];

        foreach ($data as $value) {
            if ($value['status']) {
                $group_carts['valid'][] = $value;
            } else {
                $group_carts['invalid'][] = $value;
            }
        }

        return $group_carts;
    }

    public function actionAdd()
    {
        $g_id = (int) $this->post('g_id');
        $p_id = (int) $this->post('p_id');
        $num = (int) $this->post('num');

        $GoodsCartModel = GoodsCartModel::findOne(['u_id' => $this->userId]);
        if ($GoodsCartModel) {
            $data = $GoodsCartModel->data;
            if (isset($data[$p_id])) {
                $data[$p_id]['num'] += $num;
            } else {
                $data[$p_id] = [
                    'g_id' => $g_id,
                    'p_id' => $p_id,
                    'num' => $num,
                    'time' => date('Y-m-d H:i:s')
                ];
            }

            $GoodsCartModel->setAttribute('data', $data);
        } else {
            $GoodsCartModel = new GoodsCartModel();
            $data = [
                $p_id => [
                    'g_id' => $g_id,
                    'p_id' => $p_id,
                    'num' => $num,
                    'time' => date('Y-m-d H:i:s')
                ]
            ];

            $GoodsCartModel->setAttributes([
                'u_id' => $this->userId,
                'data' => $data
            ]);
        }

        $times = array_column($data, 'time');
        array_multisort($times, SORT_DESC, $data);

        if ($GoodsCartModel->save()) {
            return Common::response(1, 'Success', $data);
        } else {
            return Common::response(0, 'Faild', $data);
        }
    }

    public function actionEdit()
    {
        $g_id = $this->post('g_id');
        $p_id = $this->post('p_id');
        $num = $this->post('num');

        $GoodsCartModel = GoodsCartModel::findOne(['u_id' => $this->userId]);
        if ($GoodsCartModel) {
            $data = $GoodsCartModel->data;
            if (isset($data[$p_id])) {
                $data[$p_id]['num'] = $num;
            } else {
                $data[$p_id] = [
                    'g_id' => $g_id,
                    'p_id' => $p_id,
                    'num' => $num,
                    'time' => date('Y-m-d H:i:s')
                ];
            }

            $GoodsCartModel->setAttribute('data', $data);
        } else {
            $GoodsCartModel = new GoodsCartModel();
            $data = [
                $p_id => [
                    'g_id' => $g_id,
                    'p_id' => $p_id,
                    'num' => $num,
                    'time' => date('Y-m-d H:i:s')
                ]
            ];

            $GoodsCartModel->setAttributes([
                'u_id' => $this->userId,
                'data' => $data
            ]);
        }

        $times = array_column($data, 'time');
        array_multisort($times, SORT_DESC, $data);

        if ($GoodsCartModel->save()) {
            return Common::response(1, 'Success', $data);
        } else {
            return Common::response(0, 'Faild', $data);
        }
    }

    public function actionDel()
    {
        $p_id_str = $this->post('p_ids');

        $GoodsCartModel = GoodsCartModel::findOne(['u_id' => $this->userId]);
        if ($GoodsCartModel) {
            $data = $GoodsCartModel->data;

            $p_ids = explode(',', $p_id_str);
            foreach ($p_ids as $p_id) {
                if (isset($data[$p_id])) {
                    unset($data[$p_id]);
                }
            }

            $GoodsCartModel->setAttribute('data', $data);
        }

        $times = array_column($data, 'time');
        array_multisort($times, SORT_DESC, $data);

        if ($GoodsCartModel->save()) {
            return Common::response(1, 'Success', $data);
        } else {
            return Common::response(0, 'Faild', $data);
        }
    }

}
