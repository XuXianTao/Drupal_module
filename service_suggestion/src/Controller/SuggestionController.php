<?php

namespace Drupal\service_suggestion\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\service_suggestion\Entity\SuggestionInterface;

/**
 * Class SuggestionController.
 *
 *  Returns responses for suggestion routes.
 */
class SuggestionController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a suggestion  revision.
   *
   * @param int $suggestion_revision
   *   The suggestion  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($suggestion_revision) {
    $suggestion = $this->entityManager()->getStorage('suggestion')->loadRevision($suggestion_revision);
    $view_builder = $this->entityManager()->getViewBuilder('suggestion');

    return $view_builder->view($suggestion);
  }

  /**
   * Page title callback for a suggestion  revision.
   *
   * @param int $suggestion_revision
   *   The suggestion  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($suggestion_revision) {
    $suggestion = $this->entityManager()->getStorage('suggestion')->loadRevision($suggestion_revision);
    return $this->t('Revision of %title from %date', ['%title' => $suggestion->label(), '%date' => format_date($suggestion->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a suggestion .
   *
   * @param \Drupal\service_suggestion\Entity\SuggestionInterface $suggestion
   *   A suggestion  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(SuggestionInterface $suggestion) {
    $account = $this->currentUser();
    $langcode = $suggestion->language()->getId();
    $langname = $suggestion->language()->getName();
    $languages = $suggestion->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $suggestion_storage = $this->entityManager()->getStorage('suggestion');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $suggestion->label()]) : $this->t('Revisions for %title', ['%title' => $suggestion->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all suggestion revisions") || $account->hasPermission('administer suggestion entities')));
    $delete_permission = (($account->hasPermission("delete all suggestion revisions") || $account->hasPermission('administer suggestion entities')));

    $rows = [];

    $vids = $suggestion_storage->revisionIds($suggestion);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\service_suggestion\SuggestionInterface $revision */
      $revision = $suggestion_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $suggestion->getRevisionId()) {
          $link = $this->l($date, new Url('entity.suggestion.revision', ['suggestion' => $suggestion->id(), 'suggestion_revision' => $vid]));
        }
        else {
          $link = $suggestion->link($date);
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
              Url::fromRoute('entity.suggestion.translation_revert', ['suggestion' => $suggestion->id(), 'suggestion_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.suggestion.revision_revert', ['suggestion' => $suggestion->id(), 'suggestion_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.suggestion.revision_delete', ['suggestion' => $suggestion->id(), 'suggestion_revision' => $vid]),
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

    $build['suggestion_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
