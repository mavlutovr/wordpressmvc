<?php
namespace Wdpro\Sender\Mailing;

interface TargetInterface {

  public function getMailingEmail();

  public function getMailingData();

  public function getMailingHash();

  public function isCorrectMailingHash($hash);

  public function mailingUnsubscribe();
}