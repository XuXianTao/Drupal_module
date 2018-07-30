<?php

namespace Drupal\fd_tags\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\fd_tags\Entity\FdTagInterface;

/**
 * Class FdTagController.
 *
 *  Returns responses for Fd tag routes.
 */
class FdTagController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Fd tag  revision.
   *
   * @param int $fd_tag_revision
   *   The Fd tag  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($fd_tag_revision) {
    $fd_tag = $this->entityManager()->getStorage('fd_tag')->loadRevision($fd_tag_revision);
    $view_builder = $this->entityManager()->getViewBuilder('fd_tag');

    return $view_builder->view($fd_tag);
  }

  /**
   * Page title callback for a Fd tag  revision.
   *
   * @param int $fd_tag_revision
   *   The Fd tag  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($fd_tag_revision) {
    $fd_tag = $this->entityManager()->getStorage('fd_tag')->loadRevision($fd_tag_revision);
    return $this->t('Revision of %title from %date', ['%title' => $fd_tag->label(), '%date' => format_date($fd_tag->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Fd tag .
   *
   * @param \Drupal\fd_tags\Entity\FdTagInterface $fd_tag
   *   A Fd tag  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(FdTagInterface $fd_tag) {
    $account = $this->currentUser();
    $langcode = $fd_tag->language()->getId();
    $langname = $fd_tag->language()->getName();
    $languages = $fd_tag->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $fd_tag_storage = $this->entityManager()->getStorage('fd_tag');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $fd_tag->label()]) : $this->t('Revisions for %title', ['%title' => $fd_tag->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all fd tag revisions") || $account->hasPermission('administer fd tag entities')));
    $delete_permission = (($account->hasPermission("delete all fd tag revisions") || $account->hasPermission('administer fd tag entities')));

    $rows = [];

    $vids = $fd_tag_storage->revisionIds($fd_tag);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\fd_tags\FdTagInterface $revision */
      $revision = $fd_tag_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $fd_tag->getRevisionId()) {
          $link = $this->l($date, new Url('entity.fd_tag.revision', ['fd_tag' => $fd_tag->id(), 'fd_tag_revision' => $vid]));
        }
        else {
          $link = $fd_tag->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.fd_tag.translation_revert', ['fd_tag' => $fd_tag->id(), 'fd_tag_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.fd_tag.revision_revert', ['fd_tag' => $fd_tag->id(), 'fd_tag_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.fd_tag.revision_delete', ['fd_tag' => $fd_tag->id(), 'fd_tag_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['fd_tag_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
