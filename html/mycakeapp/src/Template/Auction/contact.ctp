<?php
  if (!empty($hasRated)) :?>
  <h1>取引完了。ご利用ありがとうございました。</h1>
  <?= $hasRated->ratings ?>
  <?= $hasRated->comments ?>
<?php endif; ?>

<?php if (!empty($bidinfo) && empty($hasRated)): ?>
  <h2>「<?= $bidinfo->biditem->name ?>」の配送情報</h2>
  <?php if (isset($deliverTo) && (int)$deliverTo->is_shipped === 1 && (int)$deliverTo->is_received === 1) :?>
    <h3>取引完了。取引相手を評価してください。</h3>
    <?= $this->Form->create($rating,
      ['type'=>'post',
      'url'=>['controller'=>'Ratings', 'action'=>'add']]); ?>
    <table>
      <tr>
        <th>満足度（5段階）</th>
        <td>
          <?= $this->Form->select('ratings', 
            [''=>'選択してください', '1'=>1, '2'=>2, '3'=>3, '4'=>4, '5'=>5])
          ?>
        </td>
      </tr>
      <tr>
        <th>コメント</th>
        <td><?= $this->Form->textarea('comments') ?></td>
      </tr>
    </table>
    <?php echo $this->Form->hidden('bidinfo_id', ['value' => $bidinfo->id]); ?>
     <!-- ログイン中のユーザが出品者の場合は、落札者を評価 -->
    <?php if ($authuser['id'] === $exhibitor_id) :?>
      <?php echo $this->Form->hidden('ratings_for', ['value' => $bidinfo->user_id]); ?>
    <!-- ログイン中のユーザが落札者の場合は、出品者を評価 -->
    <?php elseif ($authuser['id'] === $bidder_id) : ?>
      <?php echo $this->Form->hidden('ratings_for', ['value' => $bidinfo->biditem->user_id]); ?>
    <?php endif; ?>
    <?php echo $this->Form->hidden('rated_by', ['value' => $authuser['id']]); ?>
    <?= $this->Form->button('Submit') ?>
    <?= $this->Form->end() ?>
  <?php else: ?>
    <?php if (isset($deliverTo)): ?>
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
      <?php if ($authuser['id'] === $bidder_id): ?>
        <?php if ((int)$deliverTo->is_shipped === 1): ?>
          <h3>出品者様が商品を発送しました。</h3>
          <a href="<?= $this->Url->build(['action'=>'itemReceived']); ?>?id=<?= $deliverTo->id; ?>" class="notification">受取完了</a>
        <?php else: ?>
          <h3>商品の発送をお待ちください。</h3>
        <?php endif; ?>
      <?php elseif ($authuser['id'] === $exhibitor_id): ?>
        <?php if ((int)$deliverTo->is_shipped === 1) :?>
          <h3>受取完了連絡待ち。</h3>
        <?php else: ?>
          <a href="<?= $this->Url->build(['action'=>'itemShipped']); ?>?id=<?= $deliverTo->id; ?>" class="notification">発送完了</a>
        <?php endif; ?>
      <?php endif; ?>
    <?php endif; ?>
    <?php if ($authuser['id'] === $bidder_id && !isset($deliverTo)): ?>
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
    <?php if ($authuser['id'] === $exhibitor_id && !isset($deliverTo)): ?>
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
  <?php endif; ?>
<?php elseif (empty($bidinfo)): ?>
  <h2>※落札情報はありません。</h2>
<?php endif; ?>
