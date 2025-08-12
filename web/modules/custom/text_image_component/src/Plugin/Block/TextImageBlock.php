<?php

namespace Drupal\text_image_component\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Text & Image' block.
 *
 * @Block(
 *   id = "text_image_block",
 *   admin_label = @Translation("Text & Image Component"),
 *   category = @Translation("Custom")
 * )
 */
class TextImageBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'title' => '',
      'body' => '',
      'image_fid' => NULL,
      'image_position' => 'left',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $this->configuration['title'],
    ];

    $form['body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Text'),
      '#default_value' => $this->configuration['body'],
    ];

    $form['image_fid'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Image'),
      '#upload_location' => 'public://text_image_component/',
      '#default_value' => $this->configuration['image_fid'] ? [$this->configuration['image_fid']] : NULL,
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg gif'],
      ],
    ];

    $form['image_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Image position'),
      '#options' => [
        'left' => $this->t('Left'),
        'right' => $this->t('Right'),
      ],
      '#default_value' => $this->configuration['image_position'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
  $this->configuration['title'] = $form_state->getValue('title');
  $this->configuration['body'] = $form_state->getValue('body');

  $fid = $form_state->getValue('image_fid');
  $this->configuration['image_fid'] = is_array($fid) && !empty($fid[0]) ? $fid[0] : NULL;

  // Make the file permanent so it's not cleaned up by cron.
  if (!empty($this->configuration['image_fid'])) {
    $file = \Drupal\file\Entity\File::load($this->configuration['image_fid']);
    if ($file) {
      $file->setPermanent();
      $file->save();
    }
  }

  $this->configuration['image_position'] = $form_state->getValue('image_position');
}

  /**
   * {@inheritdoc}
   */
  public function build() {
  $image_url = NULL;
  if ($this->configuration['image_fid']) {
    $file = \Drupal\file\Entity\File::load($this->configuration['image_fid']);
    if ($file) {
      $image_url = file_create_url($file->getFileUri());
    }
  }

  return [
    '#theme' => 'text_image_component',
    '#title' => $this->configuration['title'],
    '#body' => $this->configuration['body'],
    '#image_url' => $image_url,
    '#image_position' => $this->configuration['image_position'],
    // no '#attached' for CSS here — theme will provide styles.
  ];
 }
}
