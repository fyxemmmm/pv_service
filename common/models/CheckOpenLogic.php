<?php

namespace common\models;

use Yii;

class CheckOpenLogic
{
    public function check()
    {
        $headers = Yii::$app->request->headers;
        $os = $headers->get('os', '');
        $platform = $headers->get('platform', '');
        $version = $headers->get('version', '');
        $name = $headers->get('name', '');
        $open_channel = $headers->get('openChannel', '');

        switch ($os) {
            case 'android':
                $os_type = 1;
                break;
            case 'xijin_android':
                $os_type = 1;
                break;
            case 'xijin_ios':
                $os_type = 2;
                break;
            case 'ios':
                $os_type = 2;
                break;
            default:
                $os_type = 0;
        }

        if ($open_channel) {
            return true;
        }

        if (1 == $os_type) {
            $AppVersionModel = AppVersionModel::findOne([
                'name' => $name,
                'os' => $os_type,
                'channel' => $platform,
                'version' => $version
            ]);

            if ($AppVersionModel) {
                $auditing = $AppVersionModel->auditing;
                if (0 === (int) $auditing) {
                    return true;
                }
            }

        } else if (2 == $os_type) {
            $AppVersionModel = AppVersionModel::findOne([
                'os' => $os_type,
                'version' => $version
            ]);

            if ($AppVersionModel) {
                $auditing = $AppVersionModel->auditing;
                if (1 === (int) $auditing) {
                    return true;
                }
            }
        }

        return false;
    }
}
