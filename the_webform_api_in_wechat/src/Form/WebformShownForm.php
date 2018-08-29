<?php

namespace Drupal\the_webform_api_in_wechat\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Class WebformShownForm.
 */
class WebformShownForm extends ConfigFormBase
{

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return [
            'the_webform_api_in_wechat.webformshown',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'webform_shown_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('the_webform_api_in_wechat.webformshown');
        $form['table'] = [
            '#type' => 'table',
            '#header' => [
                t('开屏显示'),
                t('标题'),
                t('问卷'),
                t('状态'),
                t('问卷开始时间'),
                t('问卷结束时间'),
                t('创建时间')
            ],
            '#empty' => t('目前没有开屏问卷...')
        ];
        $category_query = \Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')
            ->getQuery();
        $category_id = $category_query
            ->condition('name', '开屏调研')
            ->condition('vid', 'webform_type')
            ->execute();
        $category_id = array_values($category_id)[0];

        /** @var \Drupal\Core\Database\Connection $db */
        $db = \Drupal::database();
        $query = $db->select('node__webform_type', 'nwt');
        $query->fields('nwt', ['entity_id'])
            ->condition('webform_type_target_id', $category_id);
        $ids = $query->execute()->fetchCol();

        /** @var Node[] $nodes */
        $nodes = Node::loadMultiple($ids);

        $origin_selected = null;
        foreach ($nodes as $k => $node) {
            $webform = get_webform($node);
            $checked = $node->get('webform_shown_page')->value;
            if (!$checked) {
                $checked = null;
            } else {
                $origin_selected = $k;
            }
            if ($checked === true || $checked === 1) $origin_selected = $k;
            $form['table'][$k] = [
                'selected' => [
                    '#type' => 'radio',
                    '#id' => 'webform_shown_page_' . $k,
                    '#name' => 'webform_shown_selected',
                    '#return_value' => $k,
                    '#attributes' => [
                        'checked' => $checked
                    ]
                ],
                'title' => [
                    '#type' => 'link',
                    '#title' => t($node->getTitle()),
                    '#url' => Url::fromRoute('entity.node.edit_form', ['node' => $k])
                ],
                'webform' => [
                    '#type' => 'link',
                    '#title' => $webform?$webform->get('title'): '未设置webform',
                    '#url' => $webform?Url::fromRoute('entity.webform.edit_form', ['webform' => $webform->id()]):''
                ],
                'status' => [
                    '#type' => 'label',
                    '#title' => get_exact_value($node, 'webform', 'status')
                ],
                'time_open' => [
                    '#markup' => get_exact_value($node, 'webform', 'open')
                ],
                'time_close' => [
                    '#markup' => get_exact_value($node, 'webform', 'close')
                ],
                'created' => [
                    '#markup' => date('Y-m-d H:i:s',$node->getCreatedTime())
                ]
            ];
        }
        $form['origin_selected'] = [
            '#type' => 'value',
            '#value' => $origin_selected
        ];
        $form['#attached']['library'][] = 'the_webform_api_in_wechat/webform_shown_page';
        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}速冻apt
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        parent::submitForm($form, $form_state);

        $this->config('the_webform_api_in_wechat.webformshown')
            ->set('shown', $form_state->getValue('shown'))
            ->save();
        $category_query = \Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')
            ->getQuery();
        $category_id = $category_query
            ->condition('name', '开屏调研')
            ->condition('vid', 'webform_type')
            ->execute();
        $category_id = array_values($category_id)[0];

        /** @var \Drupal\Core\Database\Connection $db */
        $db = \Drupal::database();
        $query = $db->select('node__webform_type', 'nwt');
        $query->fields('nwt', ['entity_id'])
            ->condition('webform_type_target_id', $category_id);
        $ids = $query->execute()->fetchCol();

        /** @var Node[] $nodes */
        $nodes = Node::loadMultiple($ids);

        foreach($nodes as $node) {
            $node->set('webform_shown_page',false)->save();
        }

        $new_id = $form_state->getUserInput()['webform_shown_selected'];
        $new_node = Node::load($new_id);
        $new_node->set('webform_shown_page',true)->save();
    }

}
