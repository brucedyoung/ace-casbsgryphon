<?php

/**
 * Class ParanoiaCest.
 *
 * @group stack
 */
class ParanoiaCest {

  /**
   * Enable paranoia and log in as admin first.
   */
  public function _before(AcceptanceTester $I) {
    $I->runDrush('pm:enable paranoia');
    $I->logInWithRole('administrator');
  }

  /**
   * Module should be hidden from enable/disable.
   */
  public function testRiskyModules(AcceptanceTester $I) {
    $I->amOnPage('/admin/modules');
    $I->cantSee('Paranoia');
  }

  /**
   * User 1 can't be edited.
   */
  public function testUserOne(AcceptanceTester $I) {
    $I->amOnPage('/user/1/edit');
    $I->canSee('You must log in as this user (user/1) to modify the name, email address, and password for this account.');
    $I->amOnPage('/user/logout');

    $I->logInWithRole('site_manager');
    $I->amOnPage('/user/1/edit');
    $I->canSee('You must log in as this user (user/1) to modify the name, email address, and password for this account.');
    $I->amOnPage('/user/logout');

    $I->logInWithRole('site_builder');
    $I->amOnPage('/user/1/edit');
    $I->canSee('You must log in as this user (user/1) to modify the name, email address, and password for this account.');
    $I->amOnPage('/user/logout');

    $I->logInWithRole('site_developer');
    $I->amOnPage('/user/1/edit');
    $I->canSee('You must log in as this user (user/1) to modify the name, email address, and password for this account.');
    $I->amOnPage('/user/logout');
  }

  /**
   * The admin role can't be changed.
   */
  public function testAdminRoleLock(AcceptanceTester $I) {
    $I->amOnPage('/admin/config/people/accounts');
    $I->cantSee('Administrator role');
    $I->cantSee('This role will be automatically assigned new permissions whenever a module is enabled. Changing this setting will not affect existing permissions.');
    $I->cantSeeElement('select[name="user_admin_role"]');
  }

}
