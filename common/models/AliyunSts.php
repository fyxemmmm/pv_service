<?php
namespace common\models;

use Yii;

use AliCloud\Core\Profile\DefaultProfile;
use AliCloud\Core\DefaultAcsClient;
use AliCloud\Core\Exception\ServerException;
use AliCloud\Core\Exception\ClientException;
use AliCloud\STS\AssumeRoleRequest;
  
define("REGION_ID", "cn-shanghai");
define("ENDPOINT", "sts.cn-shanghai.aliyuncs.com"); //根据实际情况更改配置
  
// 只允许子用户使用角色
DefaultProfile::addEndpoint(REGION_ID, REGION_ID, "Sts", ENDPOINT);
$profile = DefaultProfile::getProfile(REGION_ID, "<acccess-key-id>", "<access-key-secret>");
$client = new DefaultAcsClient($profile);
  
// 角色资源描述符，在RAM的控制台的资源详情页上可以获取
$roleArn = "<role-arn>";
  
// 在扮演角色(AssumeRole)时，可以附加一个授权策略，进一步限制角色的权限；
// 详情请参考《RAM使用指南》
// 此授权策略表示读取所有OSS的只读权限
$policy=<<<POLICY
{
  "Statement": [
    {
      "Action": [
        "oss:Get*",
        "oss:List*"
      ],
      "Effect": "Allow",
      "Resource": "*"
    }
  ],
  "Version": "1"
}
POLICY;
  
$request = new AssumeRoleRequest();  

// RoleSessionName即临时身份的会话名称，用于区分不同的临时身份
// 您可以使用您的客户的ID作为会话名称
$request->setRoleSessionName("client_name");
$request->setRoleArn($roleArn);
$request->setPolicy($policy);
$request->setDurationSeconds(3600);

try {
    $response = $client->getAcsResponse($request);
    print_r($response);
} catch(ServerException $e) {
    print "Error: " . $e->getErrorCode() . " Message: " . $e->getMessage() . "\n";
} catch(ClientException $e) {
    print "Error: " . $e->getErrorCode() . " Message: " . $e->getMessage() . "\n";
}