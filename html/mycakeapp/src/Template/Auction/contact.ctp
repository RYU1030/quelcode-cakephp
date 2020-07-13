<?php if (!empty($bidinfo)): ?>
  <h2>「<?= $bidinfo->biditem->name ?>」の配送情報</h2>
  <?php if ($authuser['id'] === $bidder_id): ?>
    <?php if (isset($deliverTo)) : ?>
      <table class="vertical-table">
        <tr>
          <th scope="row">受取人名前</th>
          <td><?= $deliverTo->receiver_name ?></td>
        </tr>
        <tr>
          <th scope="row">配送先住所</th>
          <td><?= $deliverTo->address ?></td>
        </tr>
        <tr>
          <th scope="row">受取人連絡先</th>
          <td><?= $deliverTo->mobile_number ?></td>
        </tr>
      </table>
    <?php else: ?>
      <?= $this->Form->create($deliveryInfo, ['action' => 'delivery']) ?>
      <fieldset>
        <legend>※配送先情報を入力：</legend>
        <?php
          echo $this->Form->hidden('bidinfo_id', ['value' => $bidinfo->id]);
          echo $this->Form->hidden('user_id', ['value' => $authuser['id']]);
          echo '<p><strong>USER: ' . $authuser['username'] . '</strong></p>';
          echo $this->Form->control('receiver_name', ['placeholder'=>'受取人の名前を入力してください。']);
          echo $this->Form->control('address', ['placeholder'=>'住所を入力してください。']);
          echo $this->Form->control('mobile_number', ['placeholder'=>'000-0000-0000 のフォーマットで入力してください。']);
          echo $this->Form->hidden('is_shipped', ['value' => 0]);
          echo $this->Form->hidden('is_received', ['value' => 0]);
        ?>
      </fieldset>
      <?= $this->Form->button(__('Submit')) ?>
      <?= $this->Form->end() ?>
    <?php endif; ?>
  <?php endif; ?>
  <?php if ($authuser['id'] === $exhibitor_id): ?>
    <?php if (isset($deliverTo)) : ?>
      <table class="vertical-table">
        <tr>
          <th scope="row">受取人名前</th>
          <td><?= $deliverTo->receiver_name ?></td>
        </tr>
        <tr>
          <th scope="row">配送先住所</th>
          <td><?= $deliverTo->address ?></td>
        </tr>
        <tr>
          <th scope="row">受取人連絡先</th>
          <td><?= $deliverTo->mobile_number ?></td>
        </tr>
      </table>
      
    <?php else: ?>
      <table class="vertical-table">
        <tr>
          <th scope="row">受取人名前</th>
        </tr>
        <tr>
          <th scope="row">配送先住所</th>
        </tr>
        <tr>
          <th scope="row">受取人連絡先</th>
        </tr>
      </table>
    <?php endif; ?>
  <?php endif; ?>
  <?= '<hr>' ?>
  <p>メッセージ</p>
  <table cellpadding="0" cellspacing="0">
  <thead>
    <tr>
      <th scope="col">送信者</th>
      <th class="main" scope="col">メッセージ</th>
      <th scope="col">送信時間</th>
    </tr>
  </thead>
  <tbody>
  <?php if (!empty($messages)): ?>
    <?php foreach ($messages as $msg): ?>
    <tr>
      <td><?= h($msg->user->username) ?></td>
      <td><?= h($msg->message) ?></td>
      <td><?= h($msg->created) ?></td>
    </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr><td colspan="3">※メッセージがありません。</td></tr>
  <?php endif; ?>
  </tbody>
  </table>
  <?= $this->Form->create($message) ?>
  <?= $this->Form->hidden('bidinfo_id', ['value' => $bidinfo->id]) ?>
  <?= $this->Form->hidden('user_id', ['value' => $authuser['id']]) ?>
  <?= $this->Form->textarea('message', ['rows' => 2]); ?>
  <?= $this->Form->button('Submit') ?>
  <?= $this->Form->end() ?>
  <p>落札者ID： <?= $bidder_id ?></p>
  <p>出品者ID： <?= $exhibitor_id ?></p>
<?php else: ?>
  <h2>※落札情報はありません。</h2>
<?php endif; ?>