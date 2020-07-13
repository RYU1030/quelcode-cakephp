<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event; // added.
use Exception; // added.

class AuctionController extends AuctionBaseController
{
	// デフォルトテーブルを使わない
	public $useTable = false;

	// 初期化処理
	public function initialize()
	{
		parent::initialize();
		$this->loadComponent('Paginator');
		// 必要なモデルをすべてロード
		$this->loadModel('Users');
		$this->loadModel('Biditems');
		$this->loadModel('Bidrequests');
		$this->loadModel('Bidinfo');
		$this->loadModel('Bidmessages');
		$this->loadModel('Deliveries');
		$this->loadModel('Messages');
		// ログインしているユーザー情報をauthuserに設定
		$this->set('authuser', $this->Auth->user());
		// レイアウトをauctionに変更
		$this->viewBuilder()->setLayout('auction');
	}

	// トップページ
	public function index()
	{
		// ページネーションでBiditemsを取得
		$auction = $this->paginate('Biditems', [
			'order' =>['endtime'=>'desc'], 
			'limit' => 10]);
		$this->set(compact('auction'));
	}

	// 商品情報の表示
	public function view($id = null)
	{
		// $idのBiditemを取得
		$biditem = $this->Biditems->get($id, [
			'contain' => ['Users', 'Bidinfo', 'Bidinfo.Users']
		]);
		// オークション終了時の処理
		if ($biditem->endtime < new \DateTime('now') and $biditem->finished == 0) {
			// finishedを1に変更して保存
			$biditem->finished = 1;
			$this->Biditems->save($biditem);
			// Bidinfoを作成する
			$bidinfo = $this->Bidinfo->newEntity();
			// Bidinfoのbiditem_idに$idを設定
			$bidinfo->biditem_id = $id;
			// 最高金額のBidrequestを検索
			$bidrequest = $this->Bidrequests->find('all', [
				'conditions'=>['biditem_id'=>$id], 
				'contain' => ['Users'],
				'order'=>['price'=>'desc']])->first();
			// Bidrequestが得られた時の処理
			if (!empty($bidrequest)){
				// Bidinfoの各種プロパティを設定して保存する
				$bidinfo->user_id = $bidrequest->user->id;
				$bidinfo->user = $bidrequest->user;
				$bidinfo->price = $bidrequest->price;
				$this->Bidinfo->save($bidinfo);
			}
			// Biditemのbidinfoに$bidinfoを設定
			$biditem->bidinfo = $bidinfo;		
		}
		// Bidrequestsからbiditem_idが$idのものを取得
		$bidrequests = $this->Bidrequests->find('all', [
			'conditions'=>['biditem_id'=>$id], 
			'contain' => ['Users'],
			'order'=>['price'=>'desc']])->toArray();
		// オブジェクト類をテンプレート用に設定
		$this->set(compact('biditem', 'bidrequests'));
	}

	// 出品する処理
	public function add()
	{
		// Biditemインスタンスを用意
		$biditem = $this->Biditems->newEntity();
		// POST送信時の処理
		if ($this->request->is('post')) {
			// アップロードされた画像名を変数$fileに代入
			$file = $this->request->getData('image_name');
			// 拡張子の取得
			$ext = substr(strtolower(strrchr($file['name'], '.')), 1);
			// 許可する拡張子を定義
			$arr_ext = array('jpg', 'gif', 'png');
			// 拡張子が許可されたものの場合は、画像保存に移行
			if (in_array($ext, $arr_ext)) {
				$fileName = date('YmdHis') . $file['name'];
				$filePath = WWW_ROOT . 'img/biditems/' . $fileName;
				// 画像保存に成功した場合は、他の入力値も保存する
				if (move_uploaded_file($file['tmp_name'], $filePath)) {
					$data = array (
						'user_id'=>$this->request->getData('user_id'),
						'name'=>$this->request->getData('name'),
						'details'=>$this->request->getData('details'),
						'image_name'=>$fileName,
						'finished'=>$this->request->getData('finished'),
						'endtime'=>$this->request->getData('endtime'),
					);
					// $biditemにフォームの送信内容を反映
					$biditem = $this->Biditems->patchEntity($biditem, $data);
					// $biditemを保存する
					if ($this->Biditems->save($biditem)) {
						// 成功時のメッセージ
						$this->Flash->success(__('保存しました。'));
						// トップページ（index）に移動
						return $this->redirect(['action' => 'index']);
					}
					// データ保存失敗時のメッセージ
					$this->Flash->error(__('保存に失敗しました。もう一度入力してください。'));		
				}
				// 画像アップロードに失敗した場合のメッセージ
				$this->Flash->error(__('出品画像の保存に失敗しました。再度アップロードしてください。'));
			}
			// アップロードされた画像の拡張子が許可されたものでなかった場合のメッセージ
			$this->Flash->error(__('保存に失敗しました。出品画像は、「jpg」、「gif」、「png」いずれかの拡張子でアップロードしてください。'));
		}
		// 値を保管
		$this->set(compact('biditem'));
	}

	// 入札の処理
	public function bid($biditem_id = null)
	{
		// 入札用のBidrequestインスタンスを用意
		$bidrequest = $this->Bidrequests->newEntity();
		// $bidrequestにbiditem_idとuser_idを設定
		$bidrequest->biditem_id = $biditem_id;
		$bidrequest->user_id = $this->Auth->user('id');
		// POST送信時の処理
		if ($this->request->is('post')) {
			// $bidrequestに送信フォームの内容を反映する
			$bidrequest = $this->Bidrequests->patchEntity($bidrequest, $this->request->getData());
			// Bidrequestを保存
			if ($this->Bidrequests->save($bidrequest)) {
				// 成功時のメッセージ
				$this->Flash->success(__('入札を送信しました。'));
				// トップページにリダイレクト
				return $this->redirect(['action'=>'view', $biditem_id]);
			}
			// 失敗時のメッセージ
			$this->Flash->error(__('入札に失敗しました。もう一度入力下さい。'));
		}
		// $biditem_idの$biditemを取得する
		$biditem = $this->Biditems->get($biditem_id);
		$this->set(compact('bidrequest', 'biditem'));
	}
	
	// 落札者とのメッセージ
	public function msg($bidinfo_id = null)
	{
		// Bidmessageを新たに用意
		$bidmsg = $this->Bidmessages->newEntity();
		// POST送信時の処理
		if ($this->request->is('post')) {
			// 送信されたフォームで$bidmsgを更新
			$bidmsg = $this->Bidmessages->patchEntity($bidmsg, $this->request->getData());
			// Bidmessageを保存
			if ($this->Bidmessages->save($bidmsg)) {
				$this->Flash->success(__('保存しました。'));
			} else {
				$this->Flash->error(__('保存に失敗しました。もう一度入力下さい。'));
			}
		}
		try { // $bidinfo_idからBidinfoを取得する
			$bidinfo = $this->Bidinfo->get($bidinfo_id, ['contain'=>['Biditems']]);
		} catch(Exception $e){
			$bidinfo = null;
		}
		// Bidmessageをbidinfo_idとuser_idで検索
		$bidmsgs = $this->Bidmessages->find('all',[
			'conditions'=>['bidinfo_id'=>$bidinfo_id],
			'contain' => ['Users'],
			'order'=>['created'=>'desc']]);
		$this->set(compact('bidmsgs', 'bidinfo', 'bidmsg'));
	}

	// 落札情報の表示
	public function home()
	{
		// 自分が落札したBidinfoをページネーションで取得
		$bidinfo = $this->paginate('Bidinfo', [
			'conditions'=>['Bidinfo.user_id'=>$this->Auth->user('id')], 
			'contain' => ['Users', 'Biditems'],
			'order'=>['created'=>'desc'],
			'limit' => 10])->toArray();
		$this->set(compact('bidinfo'));
	}

	// 出品情報の表示
	public function home2()
	{
		// 自分が出品したBiditemをページネーションで取得
		$biditems = $this->paginate('Biditems', [
			'conditions'=>['Biditems.user_id'=>$this->Auth->user('id')], 
			'contain' => ['Users', 'Bidinfo'],
			'order'=>['created'=>'desc'],
			'limit' => 10])->toArray();
		$this->set(compact('biditems'));
	}

	// 取引成立後の画面
	public function contact($bidinfo_id = null)
	{
		// idが$bidinfo_idのBidinfoを変数$bidinfoに格納
		try {
			$bidinfo = $this->Bidinfo->get($bidinfo_id, [
			'contain' => ['Biditems', 'Biditems.Users']
		]);

		// 出品者ID、落札者IDをそれぞれ定義
		$exhibitor_id = $bidinfo->biditem->user_id;
		$bidder_id = $bidinfo->user_id;

		// 上の二つをアクセスを許可するユーザのIDに設定し配列$permitted_idに格納
		$permitted_id = array($exhibitor_id, $bidder_id);

		// ログイン中のユーザIDが$permitted_idに含まれない場合は、アクセスを許可せずindexにリダイレクト
		if (!in_array($this->Auth->user('id'), $permitted_id)) {
			return $this->redirect(['action' => 'index']);
		}
		// Messageを新たに用意
		$message = $this->Messages->newEntity();
		// $deliveryを
		$deliveryInfo = $this->Deliveries->newEntity();
		// POST送信時の処理
		if ($this->request->is('post')) {
			// 送信されたフォームで$bidmsgを更新
			$message = $this->Messages->patchEntity($message, $this->request->getData());
			// Messageを保存
			if ($this->Messages->save($message)) {
				$this->Flash->success(__('保存しました。'));
			} else {
				$this->Flash->error(__('保存に失敗しました。もう一度入力下さい。'));
			}
		}
		// bidinfo_idが$bidinfo_idの$deliverToを取得する
		try {
			$deliverTo = $this->Deliveries->find('all', [
			'conditions'=>['bidinfo_id'=>$bidinfo_id]
			])->first();
		} catch (Exception $e) {
			$deliverTo = null;
		}
			
		// Messageをbidinfo_idとuser_idで検索
		$messages = $this->Messages->find('all',[
			'conditions'=>['bidinfo_id'=>$bidinfo_id],
			'contain' => ['Users'],
			'order'=>['created'=>'asc']]);

		$this->set(compact(
			'bidinfo_id', 'message', 'messages', 'deliveryInfo', 'deliverTo',
			'bidinfo', 'permitted_id', 'exhibitor_id', 'bidder_id'
		));
		
		} catch(Exception $e) {
			$bidinfo = null;
		}	
	}

	public function delivery()
	{
		$deliveryInfo = $this->Deliveries->newEntity();
		// POST送信時の処理
		if ($this->request->is('post')) {
			// 送信されたフォームで$bidmsgを更新
			$deliveryInfo = $this->Deliveries->patchEntity($deliveryInfo, $this->request->getData());
			// Deliveryを保存
			if ($this->Deliveries->save($deliveryInfo)) {
				$this->Flash->success(__('保存しました。'));
				return $this->redirect(['action' => 'contact', $deliveryInfo->bidinfo_id]);
			} else {
				$this->Flash->error(__('保存に失敗しました。もう一度入力下さい。'));
			}
		}
	}

	public function itemShipped() 
	{
		$deliveryId = $this->request->query['id'];
		$deliveryInfo = $this->Deliveries->get($deliveryId);
		$deliveryInfo->is_shipped = 1;
		$this->Deliveries->save($deliveryInfo);

		return $this->redirect(['action'=>'contact', $deliveryInfo->bidinfo_id]);
	}

	public function itemReceived()
	{
		$deliveryId = $this->request->query['id'];
		$deliveryInfo = $this->Deliveries->get($deliveryId);
		$deliveryInfo->is_received = 1;
		$this->Deliveries->save($deliveryInfo);

		return $this->redirect(['action'=>'contact', $deliveryInfo->bidinfo_id]);
	}

}
