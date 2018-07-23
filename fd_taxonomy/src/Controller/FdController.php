<?php

namespace Drupal\fd_taxonomy\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FdController.
 */
class FdController extends ControllerBase
{
    public function init_start(&$form, FormStateInterface &$form_state)
    {
        /*
         * 删除分类术语fd_category
         */
        $entityManager = \Drupal::getContainer()->get('entity_type.manager');
        $fd_vocabulary = $entityManager
            ->getStorage('taxonomy_vocabulary')
            ->load(FD_TAX);
        if (!empty($fd_vocabulary)) {
            if (!$fd_vocabulary->access('delete')) return new Response('没有删除术语表的权限',403);
            $fd_vocabulary->delete();
        }
        /*
         * 删除全部分类，避免tid冲突
         */
        $fd_terms_id = \Drupal::getContainer()->get('entity.query')
            ->get('taxonomy_term')
            ->execute();
        $fd_terms = $entityManager->getStorage('taxonomy_term')
            ->loadMultiple($fd_terms_id);
        foreach ($fd_terms as $fd_term) {
            $fd_term->delete();
        }
        /*
         * 新建分类术语vocabulary
         */
        if (!$fd_vocabulary->access('create')) return new Response('没有新建术语表的权限',403);
        $entityManager
            ->getStorage('taxonomy_vocabulary')
            ->create([
                'name' => FD_TAX,
                'vid' => FD_TAX,
                'description' => t('多平台分类')
            ])
            ->save();
        $result = db_select('fd_category', 'fd')
            ->fields('fd', [
                'cate_id',
                'name',
                'weight',
                'pid',
                'group_id',
                'module'
            ])
            ->execute();
        if (!empty($result)) {
            foreach ($result as $item) {
                dpm($item,'$result');
                $new_term = Term::create([
                    'tid' => $item->cate_id,
                    'vid' => FD_TAX,
                    'name' => $item->name,
                    'weight' => $item->weight,
                    'parent' => $item->pid,
                    'group_id' => $item->group_id,
                    'module' => $item->module,
                ]);
                if (!$new_term->access('create')) return new Response('没有新建分类的权限',403);
                $new_term->save();
            }
        }
        $result_form = [
            '#markup' => t('初始化完成')
        ];
        return $result_form;
//        $response = new AjaxResponse();
//        $response->addCommand(new ReplaceCommand('#fd_taxonomy-init-form',t('初始化完成')));
//        return $response;
    }

    public function create_fd(&$form, FormStateInterface &$form_state)
    {
        /*
         * 新建分类术语vocabulary
         */
        $entityManager = \Drupal::getContainer()->get('entity_type.manager');
        $entityManager
            ->getStorage('taxonomy_vocabulary')
            ->create([
                'name' => FD_TAX,
                'vid' => FD_TAX,
                'description' => t('多平台分类')
            ])
            ->save();
        return ['#markup' => t('分类术语表`fd_category`新建完成')];
    }

    public function filterCallback(&$form, FormStateInterface &$form_state)
    {
        return $form;
    }
}
