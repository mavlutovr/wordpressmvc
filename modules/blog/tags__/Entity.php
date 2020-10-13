<?php
namespace Wdpro\Blog\Tags;

/**
 * Основная сущность модуля
 */
class Entity extends \Wdpro\BasePage {


  public function getPostNamePrefix() {
    return 'tags/';
  }
}