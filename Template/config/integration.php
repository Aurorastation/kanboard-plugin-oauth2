<h3><i class="fa fa-lock fa-fw" aria-hidden="true"></i><?= t('OAuth2 Authentication') ?></h3>
<div class="panel">
    <?= $this->form->label(t('Callback URL'), 'oauth2_callback_url') ?>
    <input type="text" class="auto-select" readonly="readonly" value="<?= $this->url->href('OAuthController', 'handler', array('plugin' => 'OAuth2'), false, '', true) ?>"/>

    <?= $this->form->label(t('Client ID'), 'oauth2_client_id') ?>
    <?= $this->form->password('oauth2_client_id', $values) ?>

    <?= $this->form->label(t('Client Secret'), 'oauth2_client_secret') ?>
    <?= $this->form->password('oauth2_client_secret', $values) ?>

    <?= $this->form->label(t('IPB Base URL'), 'oauth2_ipb_base_url') ?>
    <?= $this->form->text('oauth2_ipb_base_url', $values) ?>
    <p class="form-help"><?= t('Make sure to include the trailing / in the URL') ?></p>

    <?= $this->form->label(t('IPB API Key'), 'oauth2_ipb_api_key') ?>
    <?= $this->form->password('oauth2_ipb_api_key', $values) ?>
    <p class="form-help"><?= t('This API Key needs to have GET access to individual members') ?></p>

    <?= $this->form->label(t('Scopes'), 'oauth2_scopes') ?>
    <?= $this->form->text('oauth2_scopes', $values) ?>

    <?= $this->form->hidden('oauth2_account_creation', array('oauth2_account_creation' => 0)) ?>
    <?= $this->form->checkbox('oauth2_account_creation', t('Allow Account Creation'), 1, isset($values['oauth2_account_creation']) && $values['oauth2_account_creation'] == 1) ?>

    <?= $this->form->label(t('Allow account creation only for those domains'), 'oauth2_email_domains') ?>
    <?= $this->form->text('oauth2_email_domains', $values) ?>
    <p class="form-help"><?= t('Use a comma to enter multiple domains: domain1.tld, domain2.tld') ?></p>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue"/>
    </div>
</div>
