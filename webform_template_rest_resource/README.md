# Webform Template API

### 备注：
    模块在基础接口上为Template创建了Webform Template内容类型以管理额外字段，同时保证在修改webform模板(新增或移除)的时候新建和删除对应的Webform Tepmlate的node节点类型

## 1.获取所有模板

##### 访问地址 `{SERVER}/api/webform/templates?_format=json`

##### 请求方式 `GET`

##### 请求参数
|参数|是否必须|备注
|:---:|:----:|:---:
|_format|是|默认_json，确定返回格式
|search|否|搜索包含关键字标题、问题的模板
|category|否|搜索某分类下模板
|limit|否|结果每页的模板数量(默认10)
|page|否|第几页结果(默认0)
|only_cover|否|结果是否不需要详细的字段信息


##### 样例
请求地址 `api/webform/templates?_format=json&only_cover=false&limit=5&page=0&search=contact`

返回结果
```json
{
    "total": 3,
    "page_size": 5,
    "page": 0,
    "list": {
        "template_contact": {
            "wid": "template_contact",
            "title": "Contact Us",
            "description": "A basic contact webform template.",
            "status": "closed",
            "open_time": 0,
            "close_time": 0,
            "cover_img": "http://nature.drupal/sites/default/files/2018-08/event-arrow-right.png",
            "settings": {
                "limit_total": null,
                "have_submited": 0
            },
            "elements": {
                "name": {
                    "title": "Your Name",
                    "type": "textfield",
                    "required": true
                },
                "email": {
                    "title": "Your Email",
                    "type": "email",
                    "required": true
                },
                "subject": {
                    "title": "Subject",
                    "type": "textfield",
                    "required": true
                },
                "message": {
                    "title": "Message",
                    "type": "textarea",
                    "required": true
                }
            }
        },
        "template_job_application": {
            "wid": "template_job_application",
            "title": "Job Application",
            "description": "A job application webform template.",
            "status": "closed",
            "open_time": 0,
            "close_time": 0,
            "cover_img": null,
            "settings": {
                "limit_total": null,
                "have_submited": 0
            },
            "elements": {
                "information": {
                    "title": "Your Information",
                    "type": "fieldset",
                    "children": {
                        "first_name": {
                            "title": "First Name",
                            "type": "textfield",
                            "required": true
                        },
                        "last_name": {
                            "title": "Last Name",
                            "type": "textfield",
                            "required": true
                        },
                        "gender": {
                            "type": "radios",
                            "title": "Gender",
                            "options": "gender",
                            "required": true
                        }
                    }
                },
                "contact_information": {
                    "title": "Contact Information",
                    "type": "fieldset",
                    "children": []
                },
                "resume": {
                    "title": "Your Resume",
                    "type": "fieldset",
                    "children": {
                        "resume_method": {
                            "type": "radios",
                            "options": {
                                "attach": "Attach resume file",
                                "paste": "Paste your resume"
                            },
                            "prefix": "<div class=\"container-inline\">",
                            "suffix": "</div>",
                            "default_value": "attach"
                        },
                        "resume_text": {
                            "type": "textarea",
                            "title": "Resume",
                            "title_display": "invisible",
                            "states": {
                                "visible": {
                                    ":input[name=\"resume_method\"]": {
                                        "value": "paste"
                                    }
                                },
                                "required": {
                                    ":input[name=\"resume_method\"]": {
                                        "value": "paste"
                                    }
                                },
                                "enabled": {
                                    ":input[name=\"resume_method\"]": {
                                        "value": "paste"
                                    }
                                }
                            }
                        }
                    }
                }
            }
        },
        "template_registration": {
            "wid": "template_registration",
            "title": "Registration",
            "description": "A registration webform template.",
            "status": "closed",
            "open_time": 0,
            "close_time": 0,
            "cover_img": null,
            "settings": {
                "limit_total": null,
                "have_submited": 0
            },
            "elements": {
                "personal_information": {
                    "title": "Your Personal Information",
                    "type": "fieldset",
                    "children": {
                        "first_name": {
                            "title": "First Name",
                            "type": "textfield",
                            "required": true
                        },
                        "last_name": {
                            "title": "Last Name",
                            "type": "textfield",
                            "required": true
                        }
                    }
                },
                "contact_information": {
                    "title": "Your Contact Information",
                    "type": "fieldset",
                    "children": []
                },
                "mailinglist": {
                    "title": "Mailing List",
                    "type": "fieldset",
                    "children": {
                        "subscribe": {
                            "title": "Please subscribe me to your mailing list.",
                            "type": "checkbox"
                        }
                    }
                },
                "additional_information": {
                    "title": "Additional Information",
                    "type": "fieldset",
                    "open": true,
                    "children": {
                        "notes": {
                            "title": "Comments",
                            "type": "textarea"
                        }
                    }
                }
            }
        }
    }
}
```

## 2.获取某个模板

##### 访问地址 `{SERVER}/api/webform/template/{wid}?_format=json`

##### 请求方式 `GET`

##### 请求参数
|参数|是否必须|备注
|:---:|:----:|:---:
|_format|是|默认_json，确定返回格式

##### 样例
请求地址 `api/webform/template/template_contact?_format=json`

返回结果
```json
{
    "wid": "template_contact",
    "title": "Contact Us",
    "description": "A basic contact webform template.",
    "status": "closed",
    "open_time": 0,
    "close_time": 0,
    "cover_img": "http://nature.drupal/sites/default/files/2018-08/event-arrow-right.png",
    "settings": {
        "limit_total": null,
        "have_submited": 0
    },
    "elements": {
        "name": {
            "title": "Your Name",
            "type": "textfield",
            "required": true
        },
        "email": {
            "title": "Your Email",
            "type": "email",
            "required": true
        },
        "subject": {
            "title": "Subject",
            "type": "textfield",
            "required": true
        },
        "message": {
            "title": "Message",
            "type": "textarea",
            "required": true
        }
    }
}
```
