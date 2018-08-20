# Resource Pool API

#### 获取资源池数据

##### 请求地址 `{SEVER}/api/resource_pool/{type}?_format=json`

##### 请求类型 `GET`

##### 请求参数
|参数|是否必须|备注
|:---:|:---:|:---:|
|{type}|是|请求资源类型`rich_text` 或 `image`
|_format|是|请求数据类型，通常开放接口为json
|limit|否|请求结果每页的数据个数，默认10个
|page|否|请求结果的第几页，默认第0页
|published|否|请求的资源发布状态，`true`或`false`
|taxonomy|否|请求的资源分类名，可以为汉字

##### 请求样例

请求地址 `api/resource_pool/image?_format=json&limit=5&page=0`

返回结果
```json
{
    "total": 1,
    "page_size": "5",
    "page": "0",
    "list": {
        "1": {
            "published": true,
            "resource": "http://nature.drupal/sites/default/files/2018-08/212c82622c449d6142264eafc397b94a_0.png"
        }
    }
}
```