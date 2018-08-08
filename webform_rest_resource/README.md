# Nature Webform 表单API

## 索引

* [1. Webform表单信息](#1)
    * [1.1 获取单个表单字段信息](#1-1) 
    * [1.2 修改表单信息](#1-2) 
    * [1.3 删除表单](#1-3) 
    * [1.4 新建表单](#1-4) 
    * [1.5 获取所有表单信息](#1-5) 
* [2. Webform Submission提交结果信息](#2)
    * [2.1 获取表单下的提交结果](#2-1)
    * [2.2 获取某个提交结果的详细信息](#2-2)
    * [2.3 提交表单结果](#2-3)
                     
## 1. Webform表单信息<a id="1"></a>

### 1.1 获取单个表单字段信息<a id="1-1"></a>

#### 接口地址 `{SERVER}/api/webform/{webform_id}?_format=json`

#### 请求参数


| 参数 | 必选 | 类型 | 说明 |
| --- | --- | --- | --- |
| webform_id | 否 | string |对应表单标题/ID(不填则获取全部表单字段信息参考1.5) |

#### 请求方式 `GET`

#### 样例
##### 请求地址 `api/webform/c4f89b02707e4001852c6ef5dda56b4a?_format=json`
获取返回内容 头部 `Status: 200 OK`
```json
{
    "id": "c4f89b02707e4001852c6ef5dda56b4a",
    "title": "new-新的测试",
    "description": "Using post to chagne the description244333",
    "status": "scheduled",
    "open_time": 1533664800000,
    "close_time": 1533710768000,
    "elements": {
        "p1": {
            "type": "webform_wizard_page",
            "title": "P1",
            "open": true,
            "child": {
                "nidexinxi": {
                    "type": "details",
                    "title": "你的信息",
                    "child": {
                        "xingming": {
                            "type": "textfield",
                            "title": "姓名"
                        }
                    }
                },
                "nizuixihuandeshishenme": {
                    "type": "textarea",
                    "title": "你最喜欢的是什么"
                },
                "nizuitaoyanshenme": {
                    "type": "textarea",
                    "title": "你最讨厌什么"
                }
            }
        },
        "p2": {
            "type": "webform_wizard_page",
            "title": "P2",
            "child": {
                "yincangfendewenda": {
                    "type": "textarea",
                    "title": "隐藏分的问答"
                },
                "hello": {
                    "type": "textarea",
                    "title": "真实台号了"
                }
            }
        }
    }
}
```

### 1.2 修改表单信息<a id="1-2"></a>

#### 接口地址 `{SERVER}/api/webform/{webform_id}?_format=json`
#### 请求参数
| 参数 | 必选 | 类型 | 说明 |
| --- | --- | --- | --- |
| webform_id | 否 | string |对应表单标题/ID(不填则获取全部表单字段信息) |

#### 请求方式 `PATCH`

#### 样例
##### 请求地址 `/api/webform/c4f89b02707e4001852c6ef5dda56b4a?_format=json`
请求内容
```json
{
    "title": "new-新的测试",
    "description": "Using post to chagne the description244333",
    "status": "scheduled",
    "message": "",
    "open_time": "1533664800000",
    "close_time": "1533710768451",
    "elements": {
        "p1": {
            "type": "webform_wizard_page",
            "title": "P1",
            "open": true,
            "child": {
                "nidexinxi": {
                    "type": "details",
                    "title": "你的信息",
                    "child": {
                        "xingming": {
                            "type": "textfield",
                            "title": "姓名"
                        }
                    }
                },
                "nizuixihuandeshishenme": {
                    "type": "textarea",
                    "title": "你最喜欢的是什么"
                },
                "nizuitaoyanshenme": {
                    "type": "textarea",
                    "title": "你最讨厌什么"
                }
            }
        },
        "p2": {
            "type": "webform_wizard_page",
            "title": "P2",
            "child": {
                "yincangfendewenda": {
                    "type": "textarea",
                    "title": "隐藏分的问答"
                },
                "hello": {
                    "type": "textarea",
                    "title": "真实台号了"
                }
            }
        }
    }
}
```
返回结果 头部`Status: 201 Created`
```json
{
    "id": "c4f89b02707e4001852c6ef5dda56b4a",
    "title": "new-新的测试",
    "description": "Using post to chagne the description244333",
    "status": "scheduled",
    "open_time": 1533664800000,
    "close_time": 1533710768000,
    "elements": {
        "p1": {
            "type": "webform_wizard_page",
            "title": "P1",
            "open": true,
            "child": {
                "nidexinxi": {
                    "type": "details",
                    "title": "你的信息",
                    "child": {
                        "xingming": {
                            "type": "textfield",
                            "title": "姓名"
                        }
                    }
                },
                "nizuixihuandeshishenme": {
                    "type": "textarea",
                    "title": "你最喜欢的是什么"
                },
                "nizuitaoyanshenme": {
                    "type": "textarea",
                    "title": "你最讨厌什么"
                }
            }
        },
        "p2": {
            "type": "webform_wizard_page",
            "title": "P2",
            "child": {
                "yincangfendewenda": {
                    "type": "textarea",
                    "title": "隐藏分的问答"
                },
                "hello": {
                    "type": "textarea",
                    "title": "真实台号了"
                }
            }
        }
    }
}
```

### 1.3 删除表单<a id="1-3"></a>

#### 接口地址 `{SERVER}/api/webform/{webform_id}?_format=json`
#### 请求参数
| 参数 | 必选 | 类型 | 说明 |
| --- | --- | --- | --- |
| webform_id | 否 | string |对应表单标题/ID(不填则获取全部表单字段信息) |

#### 请求方式 `DELETE`

#### 样例
##### 请求地址 `/api/webform/test2?_format=json`
返回头部 `Status: 204 No Content` 处理成功，无内容信息

### 1.4 新建表单<a id="1-4"></a>
#### 接口地址 `{SERVER}/api/webform?_format=json`

#### 请求方式 `POST`

#### 样例
##### 请求地址 `/api/webform?_format=json`
请求内容
```json
{
    "title": "new-新的测试",
    "description": "Using post to chagne the description244333",
    "status": "scheduled",
    "message": "",
    "open_time": "1533664800000",
    "close_time": "1533710768451",
    "elements": {
        "p1": {
            "type": "webform_wizard_page",
            "title": "P1",
            "open": true,
            "child": {
                "nidexinxi": {
                    "type": "details",
                    "title": "你的信息",
                    "child": {
                        "xingming": {
                            "type": "textfield",
                            "title": "姓名"
                        }
                    }
                },
                "nizuixihuandeshishenme": {
                    "type": "textarea",
                    "title": "你最喜欢的是什么"
                },
                "nizuitaoyanshenme": {
                    "type": "textarea",
                    "title": "你最讨厌什么"
                }
            }
        },
        "p2": {
            "type": "webform_wizard_page",
            "title": "P2",
            "child": {
                "yincangfendewenda": {
                    "type": "textarea",
                    "title": "隐藏分的问答"
                },
                "hello": {
                    "type": "textarea",
                    "title": "真实台号了"
                }
            }
        }
    }
}
```

返回信息 `Status: 201 Created` 返回新建表单id
```json
"f7f06c4e10dd4ab1999e456578b7d49c"
```

### 1.5 获取所有表单信息<a id="1-5"></a>

#### 接口地址 `{SERVER}/api/webform?_format=json`
#### 请求参数
| 参数 | 必选 | 类型 | 说明 |
| --- | --- | --- | --- |
|limit|否|number|获取结果数量(默认为10) |
|page|否|number|以limit为单位，获取偏移值为page的结果(默认0)
|status|否|string|`open`/`closed`/`scheduled`获取的表单的开放情况
|search|否|string|搜索表单标题含有search字段的结果

#### 请求方式 `GET`

#### 样例
##### 请求地址 `/api/webform?_format=json`

##### 请求地址 `api/webform?_format=json&limit=3`
获取返回内容 头部 `Status: 200 OK`
```json
{
    "total": 3,
    "page_size": 3,
    "page": 0,
    "list": {
        "4584fa9b81294465bf2470b27b4fc3b9": {
            "id": "4584fa9b81294465bf2470b27b4fc3b9",
            "title": "test2333",
            "description": "Using post to chagne the description244333",
            "status": "closed",
            "open_time": 0,
            "close_time": 0,
            "elements": {
                "p1": {
                    "type": "webform_wizard_page",
                    "title": "P1",
                    "open": true,
                    "child": {
                        "nidexinxi": {
                            "type": "details",
                            "title": "你的信息",
                            "child": {
                                "xingming": {
                                    "type": "textfield",
                                    "title": "姓名"
                                }
                            }
                        },
                        "nizuixihuandeshishenme": {
                            "type": "textarea",
                            "title": "你最喜欢的是什么"
                        },
                        "nizuitaoyanshenme": {
                            "type": "textarea",
                            "title": "你最讨厌什么"
                        }
                    }
                },
                "p2": {
                    "type": "webform_wizard_page",
                    "title": "P2",
                    "child": {
                        "yincangfendewenda": {
                            "type": "textarea",
                            "title": "隐藏分的问答"
                        }
                    }
                }
            }
        },
        "4e5638034a2a4ceea592fa3f5d159082": {
            "id": "4e5638034a2a4ceea592fa3f5d159082",
            "title": "new-新的测试",
            "description": null,
            "status": "open",
            "open_time": 0,
            "close_time": 0,
            "elements": {
                "p1": {
                    "type": "webform_wizard_page",
                    "title": "P1",
                    "open": true,
                    "child": {
                        "nidexinxi": {
                            "type": "details",
                            "title": "你的信息",
                            "child": {
                                "xingming": {
                                    "type": "textfield",
                                    "title": "姓名"
                                }
                            }
                        },
                        "nizuixihuandeshishenme": {
                            "type": "textarea",
                            "title": "你最喜欢的是什么"
                        },
                        "nizuitaoyanshenme": {
                            "type": "textarea",
                            "title": "你最讨厌什么"
                        }
                    }
                },
                "p2": {
                    "type": "webform_wizard_page",
                    "title": "P2",
                    "child": {
                        "yincangfendewenda": {
                            "type": "textarea",
                            "title": "隐藏分的问答"
                        },
                        "hello": {
                            "type": "textarea",
                            "title": "真实台号了"
                        }
                    }
                }
            }
        },
        "contact": {
            "id": "contact",
            "title": "Con",
            "description": "Basic email contact webform.",
            "status": "open",
            "open_time": 0,
            "close_time": 0,
            "elements": {
                "name": {
                    "title": "Your Name",
                    "type": "textfield",
                    "required": true,
                    "default_value": "[current-user:display-name]"
                },
                "email": {
                    "title": "Your Email",
                    "type": "email",
                    "required": true,
                    "default_value": "[current-user:mail]"
                },
                "subject": {
                    "title": "Subject",
                    "type": "textfield",
                    "required": true,
                    "test": "Testing contact webform from [site:name]"
                },
                "message": {
                    "title": "Message",
                    "type": "textarea",
                    "required": true,
                    "test": "Please ignore this email."
                }
            }
        }
    }
}
```



## 2. Webform Submission提交结果信息<a id="2"></a>

### 2.1 获取表单下的提交结果<a id="2-1"></a>
####　接口地址 `{SEVER}/api/webform/{webform_id}/submission?_format=json`
#### 请求参数 
| 参数 | 必选 | 类型 | 说明 |
| --- | --- | --- | --- |
| webform_id | 是 | string |对应表单标题/ID|
|limit|否|number|获取结果数量(默认为10) |
|page|否|number|以limit为单位，获取偏移值为page的结果(默认0)
|search|否|string|搜索表单含有search字段的结果
|sticky|否|string|`true`/`false`被标记的表单结果
|locked|否|string|`true`/`false`被锁定的表单结果
|in_draft|否|string|`true`/`false`作为草稿的表单结果
|only_id|否|string|`true`/`false`是否只返回表单id而不包含字段信息

#### 请求方式 `GET`
#### 样例
##### 请求地址`api/webform/test/submission?_format=json&limit=2&only_id=true`
返回结果 头部 `Status: 200 OK`
```json
{
    "total": 2,
    "page": 0,
    "open": true,
    "start_time": "1533520800",
    "end_time": "1565092800",
    "list": [
        {
            "sid": 1,
            "created": "1533277270",
            "completed": "1533277270",
            "changed": "1533277270",
            "current_page": null,
            "remote_addr": "127.0.0.1",
            "uid": "0",
            "locked": false,
            "sticky": false,
            "in_draft": false
        },
        {
            "sid": 2,
            "created": "1533277284",
            "completed": "1533277284",
            "changed": "1533277284",
            "current_page": null,
            "remote_addr": "127.0.0.1",
            "uid": "0",
            "locked": false,
            "sticky": false,
            "in_draft": false
        }
    ]
}
```

### 2.2 获取某个提交结果的详细信息<a id="2-2"></a>
####　接口地址 `{SEVER}/api/webform/submission/{sid}?_format=json`
#### 请求参数 
| 参数 | 必选 | 类型 | 说明 |
| --- | --- | --- | --- |
|sid|是|number|表单结果的sid编号|

#### 请求方式 `GET`
#### 样例
##### 请求地址`api/webform/submission/2?_format=json`
返回结果 头部 `Status: 200 OK`
```json
{
    "sid": "2",
    "created": "1533277284",
    "completed": "1533277284",
    "changed": "1533277284",
    "current_page": null,
    "remote_addr": "127.0.0.1",
    "uid": "0",
    "locked": false,
    "sticky": false,
    "in_draft": false,
    "nizuitaoyanshenme": "鬼知2道",
    "nizuixihuandeshishenme": "水果啊2",
    "xingming": "xxt2",
    "yicangfendewenda": "隐藏答案2"
}
```

### 2.3 提交表单结果<a id="2-3"></a>
####　接口地址 `{SEVER}/api/webform/{webform_id}/submission?_format=json`
#### 请求参数 
| 参数 | 必选 | 类型 | 说明 |
| --- | --- | --- | --- |
| webform_id | 是 | string |对应表单标题/ID|

#### 请求方式 `POST`
#### 样例
##### 请求地址`api/webform/test2/submission?_format=json`
请求内容
```json
{
    "nizuitaoyanshenme": "鬼知2道",
    "nizuixihuandeshishenme": "水果啊2",
    "xingming": "xxt2",
    "yicangfendewenda": "隐藏答案2"
}
```
返回结果 头部 `Status: 201 Created`
```json
"The webform submission intest2has submitted successfully."
```

## 3. 验证码字段刷新
#### 请求接口 `{SERVER}/api/webform/captcha/generate/{webform_id}`
#### 请求参数 
| 参数 | 必选 | 类型 | 说明 |
| --- | --- | --- | --- |
| webform_id | 是 | string |需要刷新的对应表单标题/ID|

#### 请求方式 `GET`
#### 样例
##### 请求地址`api/webform/captcha/generate/test`
返回结果 头部 `Status: 200 OK` 返回生成验证码url
```json
/image-captcha-generate/99/1533631465
```