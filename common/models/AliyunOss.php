<?php
namespace common\models;

use Yii;
use OSS\OssClient;
use OSS\Core\OssException;

/**
 * 阿里云对象存储类
 * @author zhangxuan
 * @date-time 2019-2-20 
 * @email ruo123xian@gmail.com
 */
class AliyunOss
{
    private $ossClient;
    private $bucket;
    public function __construct($bucket = "xijin")
    {
        $this->bucket = $bucket;
        $this->init();
    }
    private function init(){
        $aliyun = Yii::$app->params["aliyun_oss"];
        $accessKeyId = $aliyun['AccessKeyID'];
        $accessKeySecret = $aliyun['AccessKeySecret'];
        $endpoint = $aliyun['EndPoint'];
        try {
            $this->ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        } catch (OssException $e) {
            throw new Error("阿里云存储初始化失败.{$e->getMessage()}");
        }
        
    }

    /**
     * 修改要操作的存储空间
     * @param string $bucket 存储空间名称
     */
    public function setBucket($bucket)
    {
        $this->bucket = $bucket;
        return true;
    }
    /**
     * 创建存储空间
     * @param string $bucket 存储空间名称,默认私有读写，默认标准类型。
     */
    public function createBucket($bucket)
    {
        $this->setBucket($bucket);
        return $this->ossClient->createBucket($bucket);
    }

    /**
     * 上传字符串
     * @param string $object 文件名称
     * @param string $content 文件内容
     */
    public function putObject($object, $content)
    {

        return $this->ossClient->putObject($this->bucket, $object, $content);
    }

    /**
     * 上传文件
     * @param string $object 文件名称
     * @param string $filePath 文件路径
     */
    public function uploadFile($object, $filePath)
    {
        return $this->ossClient->uploadFile($this->bucket, $object, $filePath);
    }

    /**
     * 生成可访问的url
     * @param string $object 文件名称
     * @param string $timeout 超时时间
     */

    public function signUrl($object, $timeout = 3600)
    {
        return $this->ossClient->signUrl($this->bucket, $object, $timeout);
    }


    /**
     * 判断文件是否存在
     * @param string $object 文件名称
     */
    public function doesObjectExist($object)
    {
        return $this->ossClient->doesObjectExist($this->bucket, $object);
    }

    /**
     * 删除单个对象
     * @param string $object 文件名称
     */
    public function deleteObject($object)
    {
        return $this->ossClient->deleteObject($this->bucket, $object);
    }

    /**
     * 删除多个对象
     * @param array $objects 文件名称
     */
    public function deleteObjects($objects)
    {
        return $this->ossClient->deleteObjects($this->bucket, $objects);
    }
}
