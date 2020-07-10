<h2>「<?= $bidinfo->biditem->name ?>」の配送情報</h2>
<table class="vertical-table">
  <tr>
    <th scope="row">受取人名前</th>
    <td></td>
  </tr>
  <tr>
    <th scope="row">配送先住所</th>
    <td></td>
  </tr>
  <tr>
    <th scope="row">受取人連絡先</th>
    <td></td>
  </tr>
</table>
<?= $this->Form->create() ?>
<?= $this->Form->textarea('message', ['rows'=>2]); ?>
<?= $this->Form->button('Submit') ?>
<?= $this->Form->end() ?>
<table cellpadding="0" cellspacing="0">
<thead>
	<tr>
		<th scope="col">送信者</th>
		<th class="main" scope="col">メッセージ</th>
		<th scope="col">送信時間</th>
	</tr>
</thead>
<tbody>
<?php if (!empty($bidmsgs)): ?>
	<?php foreach ($bidmsgs as $msg): ?>
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
<p>落札者ID： <?= $bidder_id ?></p>
<p>出品者ID： <?= $exhibitor_id ?></p>
