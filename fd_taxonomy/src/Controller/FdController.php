<?php

namespace Drupal\fd_taxonomy\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FdController.
 */
class FdController extends ControllerBase
{

    /**
     * Import.
     * Changing the sql in fd_category to taxonomy_term_field_data
     */
    public function import()
    {
        $query = db_query("SELECT * FROM {fd_category}");
        $result = $query->fetchAll();
        foreach ($result as $item) {
            Term::create([
                'tid' => $item->cate_id,
                'vid' => 'fd_category',
                'name' => $item->name,
                'weight' => $item->weight,
                'parent' => $item->pid,
                'group_id' => $item->group_id,
                'module' => $item->module,
            ])->save();
        }
        return new Response(t('数据库处理完成'));
    }

    public function filterCallback(&$form, FormStateInterface &$form_state)
    {
        $response = new AjaxResponse();
        $response->addCommand(new ReplaceCommand('#fd_taxonomy-terms-list', $form));
        return $response;
    }
}
