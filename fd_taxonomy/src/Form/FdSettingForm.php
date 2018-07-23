<?php

namespace Drupal\fd_taxonomy\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SuggestionSettingsForm.
 *
 * @ingroup service_suggestion
 */
class FdSettingForm extends FormBase {

    public function getFormId() {
        return 'fd_taxonomy_settings';
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        // Empty implementation of the abstract submit class.
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $text_info =
            '<h5>'.
            '<ul><li> 如果存在数据表`fd_category`，且存在字段`cate_id`、`name`、`weight`、`pid`、`group_id`、`module`，则可以导入数据，<font color="red">但相应的将清除全部分类数据</font></li>'.
            '<li> 如果需要保存原有分类信息，则直接新建fd_category</li></ul>'.
            '</h5>'
        ;
        $form[$this->getFormId()] = [
            '#markup' => '初始化FD_CATEGORY',
            'buttons' => [
                '#type' => 'fieldset',
                '#title' => t('初始化分类fd_category'),
                '#id' => 'fd_taxonomy-init-form',
                '#markup' => t($text_info),
                'choose1' => [
                    '#type' => 'button',
                    '#value' => t('导入数据(*将清空全部分类数据！！！)'),
                    '#ajax' => [
                        'callback' => 'Drupal\fd_taxonomy\Controller\FdController::init_start',
                        'wrapper' => 'fd_taxonomy-init-form'
                    ]
                ],
                'choose2' => [
                    '#type' => 'button',
                    '#value' => t('新建分类术语表`fd_category`'),
                    '#ajax' => [
                        'callback' => 'Drupal\fd_taxonomy\Controller\FdController::create_fd',
                        'wrapper' => 'fd_taxonomy-init-form'
                    ]
                ]
            ]
        ];
        return $form;
    }

}
