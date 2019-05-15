<?php

namespace yuuhi666\Uploader;

class Upload{
    /**
     * アップロード完了フラグ
     *
     * @var boolean
     */
    public $uploaded;

    /**
     * プロセス完了フラグ
     *
     * @var boolean
     */
    public $processed;

    /**
     * 同一ファイル重複を許可フラグ
     *
     * @var boolean
     */
    public $duplication;

    /**
     * アップロード先ファイルまでのパスとファイル名
     *
     *
     * @var string
     */
    public $filePathName;


    /**
     * アップロード先ファイルまでのパス
     *
     *
     * @var string
     */
    public $filePath;

    /**
     * ファイル名
     *
     *
     * @var string
     */
    public $fileName;

    /**
     * パーミッション
     *
     *
     * @var integer
     */
    public $permission;

    /**
     * リサイズ許可フラグ
     *
     *
     * @var boolean
     */
    public $resize;

    /**
     * リサイズ後の横幅px
     *
     *
     * @var integer
     */
    public $resizeWidth;

    /**
     * リサイズ後の縦幅px
     *
     *
     * @var integer
     */
    public $resizeHeight;

    /**
     * 内接リサイズか外接リサイズかを判定フラグ
     *
     *
     * @var integer
     */
    public $resizeProcess;

    /**
     * 外接サイズ時におけるX座標
     *
     *
     * @var integer
     */
    public $imageSrcX;

    /**
     * 外接サイズ時におけるY座標
     *
     *
     * @var integer
     */
    public $imageSrcY;

    /**
     * 内接リサイズ判定フラグ
     *
     *
     * @var integer
     */
    const INSCRIBED_RESIZING = 0;

    /**
     * 外接リサイズ判定フラグ
     *
     *
     * @var integer
     */
    const INTERNAL_RESIZING = 1;

    /**
     * 作成した画像リソース
     *
     *
     * @var string
     */
    public $_image;

    /**
     * アップロードした画像リソース
     *
     *
     * @var string
     */
    public $image;

    /**
     * リサイズした画像リソース
     *
     *
     * @var string
     */
    public $thumb;

    // /**
    //  * imagecreatefromxxx関数
    //  *
    //  *
    //  * @var string
    //  */
    // public $imagecreatefrom;
    //
    // /**
    //  * imagexxx関数
    //  *
    //  *
    //  * @var string
    //  */
    // public $imagecreate;

    /**
     *  最終的なエラー格納
     *
     *
     * @var array
     */
    public $error;

    /**
     * $_FILE['form_feild']['temp_name']
     *
     *
     * @var string $_FILE['form_feild']['tem_pname']
     */
    public $imageSrcTempName;

    /**
     * アップロードした画像の横幅
     *
     *
     * @var integer
     */
    public $imageSrcWidth;

    /**
     * アップロードした画像の縦幅
     *
     *
     * @var integer
     */
    public $imageSrcHeight;

    /**
     * 許可する画像の横幅　(最大)
     *
     *
     * @var integer
     */
    public $imageMaxWidth;

    /**
     * 許可する画像の縦幅　(最大)
     *
     *
     * @var integer
     */
    public $imageMaxHeight;

    /**
     * HTMLのimgタグで直接使用可能な文字列　height="yyy" width="xxx"
     *
     *
     * @var string
     */
    public $stringForHTMLTag;

    /**
     * HTTP Content-type
     *
     *
     * @var string
     */
    public $contentType;

    /**
     * imagetype定数に対応する拡張子を返します
     *
     *
     * @var string
     */
    public $imageSrcMimeType;

    /**
     * アップロードした画像のimagetype定数
     *
     *
     * @var integer
     */
    public $imageSrcImageMimeTypeConstant;

    /**
     * 許可する mimeタイプに対応する　imagetype定数
     *
     * @var integer
     */
    private $mimeTypeConstantList;

    /**
     * 許可する Mime type のリスト
     *
     * @var array
     */
    public $allowedMimeTypeList;

    /**
     * コンストラクタ
     *
     * @param array $file
     * @return void
     */
    public function __construct($file)
    {
        $this->setProperty();
        $this->uploadCheck($file);

        $this->_FILE = $file;
    }

    /**
     * 各メンバ変数の値セット
     *
     * @return void
     */
    private function setProperty()
    {
        $this->uploaded = true;

        $this->processed = true;

        $this->duplication = true;

        $this->permission = 0644;

        $this->resized = false;

        $this->imageMaxWidth = 1024;

        $this->imageMaxHeight = 1024;

        $this->resizeWidth = 120;

        $this->resizeHeight = 120;

        $this->resizeProcess = 0;

        $this->imageSrcX = 0;

        $this->imageSrcY = 0;

        $this->error = '';

        $this->allowedMimeTypeList = [
            'gif',
            'jpeg',
            'png'
        ];

        $this->mimeTypeConstantList = [
            'gif' => IMAGETYPE_GIF,
            'jpeg' => IMAGETYPE_JPEG,
            'png' => IMAGETYPE_PNG,
        ];
    }

    /**
     * ファイルアップロードチェック。アップロード確認後に画像の情報を各プロパティに格納
     *
     * @param array $file $_FILES['form_field']
     * @return void
     */
    private function uploadCheck($file)
    {
        try {
            if (!isset($file['error']) || !is_int($file['error'])) {
                throw new \Exception('不正なデータです。');
            }

            switch ($file['error']) {
              case UPLOAD_ERR_OK:
                  break;
              case UPLOAD_ERR_NO_FILE://ファイルが選択されていない
                  throw new \Exception('ファイルが選択されていません。');
              case UPLOAD_ERR_INI_SIZE://php.iniのアップロード上限値を超過
                  throw new \Exception('ファイルの上限サイズオーバーです。');
              case UPLOAD_ERR_FORM_SIZE://フォームでのアップロード上限値を超過(フォームにて上限値を設定していた場合)
                  throw new \Exception('ファイルの上限サイズオーバーです。');
              default://その他のエラー
                  throw new \Exception('不明なエラーです。');
            }

            if (false === $info = @getimagesize($file['tmp_name'])) {//ファイルの情報取得
                throw new \Exception('ファイル情報の取得に失敗しました。');//ファイルの情報取得エラー
            }

            $this->imageSrcTempName = $file['tmp_name'];
            $this->imageSrcWidth = $info[0];
            $this->imageSrcHeight = $info[1];
            $this->imageSrcImageMimeTypeConstant = $info[2];
            $this->stringForHTMLTag = $info[3];
            $this->contentType = $info['mime'];

        } catch (\Exception $e) {
            $this->uploaded = false;
            $this->error = $e->getMessage();
        }
    }

    /**
     * アップロードした画像の幅をチェック
     *
     *
     * @return boolean
     */
    private function sizeCheck()
    {
        if ($this->imageMaxWidth < $this->imageSrcWidth
            || $this->imageMaxHeight < $this->imageSrcHeight) {
              return false;//ファイルの幅オーバー
        }

        return true;
    }

    /**
     * アップロードした画像のmimetypeをチェック
     *
     * @param integer  imagetype定数
     * @return void
     */
    private function mimeTypeCheck()
    {
        foreach ($this->allowedMimeTypeList as $value) {
            //アプリ側で許可をしたmimetypeリストと同様のキーがmimeTypeConstantListに存在するか
            if (array_key_exists($value, $this->mimeTypeConstantList)) {//存在する
                //取得した画像のimagetype定数とアプリ側で許可をしたmimetype定数が一致しない
                if ($this->imageSrcImageMimeTypeConstant === $this->mimeTypeConstantList[$value]){
                    $this->imageSrcMimeType = image_type_to_extension($this->imageSrcImageMimeTypeConstant);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * ファイル名生成
     *
     *
     * @return void
     */
    private function createFilePathName()
    {
        if ($this->duplication) {
            //重複したデータを許す(重複しない値を生成するまでループ)
            $this->fileName = bin2hex(openssl_random_pseudo_bytes(32)) . $this->imageSrcMimeType;
            while(is_file($this->filePath . '/' . $this->fileName)){
                $this->fileName = bin2hex(openssl_random_pseudo_bytes(32)) . $this->imageSrcMimeType;
            };
        }else {
            //一意なファイル名生成
            $this->fileName = sha1_file($this->imageSrcTempName) . $this->imageSrcMimeType;
        }

        $this->filePathName = $this->filePath . '/' . $this->fileName;
    }

    /**
     * アップロード処理
     *
     * @param boolean $resize リサイズするかどうか
     * @param integer $resizeProcess 1 0
     * @param boolean $delete リサイズ後元画像を削除する
     * @return void
     */
    public function process($filePath)
    {
        try {
            if ($this->uploaded) {//アップロードが完了している

                $this->filePath = $filePath;

                if (!$this->sizeCheck()) {//サイズチェック
                    throw new \Exception('ファイルサイズオーバーです。');//サイズオーバー
                }

                if (!$this->mimeTypeCheck($this->imageSrcImageMimeTypeConstant)) {//mimetypeチェック
                    throw new \Exception('不正な画像形式です。');//拡張子エラー
                }


                if ($this->resize) {//リサイズを許可している
                    $this->resize();
                }else {

                    $this->createFilePathName();

                    fopen($this->filePathName, 'xb');//排他的にファイルを生成(既にファイルが存在している場合はWarningが発生する)

                    if (!move_uploaded_file($this->imageSrcTempName, $this->filePathName)) {//作成したファイルを上書き(移動)
                        throw new \Exception('ファイルの保存に失敗しました。');//ファイルの移動失敗
                    }
                }

                chmod($this->filePathName, $this->permission);//パーミッションを0644に設定

            }else {
                throw new \Exception('画像のアップロードが完了していません。');
            }

        } catch (\Exception $e) {
            $this->processed = false;
            $this->error = $e->getMessage();
        }

    }

    /**
     * リサイズ処理
     *
     *
     * @return void
     */
    private function resize()
    {
        ob_start();//バッファ開始

        $imagecreatefrom = str_replace('/', 'createfrom', $this->contentType);//imagecreatefrom×××関数
        $imagecreate = str_replace('/', '', $this->contentType);//image×××関数

        if (!$this->image = @$imagecreatefrom($this->imageSrcTempName)) {
            throw new \Exception('画像のリソース作成に失敗しました。');
        }

        if (self::INSCRIBED_RESIZING === $this->resizeProcess) {//内接リサイズ
            $this->inscribedResizing();
        }elseif (self::INTERNAL_RESIZING === $this->resizeProcess) {//外接リサイズ
            $this->internalResizing();
        }

        $imagecreate($this->thumb);

        $this->_image = ob_get_clean();

        imagedestroy($this->image);
        imagedestroy($this->thumb);

        $this->createFilePathName();

        $fp = fopen($this->filePathName, 'xb');

        flock($fp, LOCK_EX);

        fwrite($fp, $this->_image);

        fflush($fp);
        flock($fp, LOCK_UN);

        fclose($fp);
    }

    /**
     * 内接リサイズ
     *
     * 指定されたアスペクト比に収まるように画像をリサイズ。
     *
     */
    private function inscribedResizing()
    {
        if ($this->imageSrcWidth > $this->imageSrcHeight) {//元画像の幅が高さ以上の場合、幅に合わせて高さを縮小
            $this->resizeHeight = intval($this->resizeWidth / $this->imageSrcWidth * $this->imageSrcHeight);
        }elseif ($this->imageSrcWidth < $this->imageSrcHeight) {
            $this->resizeWidth = intval($this->resizeHeight / $this->imageSrcHeight * $this->imageSrcWidth);
        }

        $this->thumb = imagecreatetruecolor($this->resizeWidth, $this->resizeHeight);//コピー先の画像リソース取得

        imagecopyresampled($this->thumb, $this->image, 0, 0, 0, 0, $this->resizeWidth, $this->resizeHeight, $this->imageSrcWidth, $this->imageSrcHeight);//サンプリング

    }

    /**
     * 外接リサイズ
     *
     * 画像の中心から指定されたアスペクト比で画像をトリミング。
     *
     */
    private function internalResizing()
    {
        if ($this->imageSrcWidth > $this->imageSrcHeight) {
            $this->imageSrcX = ceil(($this->imageSrcWidth - $this->imageSrcHeight) * 0.5);
            $this->imageSrcY = 0;
            $this->imageSrcWidth = $this->imageSrcHeight;
        }elseif ($this->imageSrcWidth < $this->imageSrcHeight) {
            $this->imageSrcX = 0;
            $this->imageSrcY = ceil(($this->imageSrcHeight - $this->imageSrcWidth) * 0.5);
            $this->imageSrcHeight = $this->imageSrcWidth;
        }

        $this->thumb = imagecreatetruecolor($this->resizeWidth, $this->resizeHeight);//コピー先の画像リソース取得

        imagecopyresampled($this->thumb, $this->image, 0, 0, $this->imageSrcX, $this->imageSrcY, $this->resizeWidth, $this->resizeHeight, $this->imageSrcWidth, $this->imageSrcHeight);//サンプリング
    }

    public function dataURIScheme($imageMimeTypeConstant, $binaryData)
    {
        $mime = image_type_to_mime_type($imageMimeTypeConstant);
        $thumb = base64_encode($binaryData);

        return "data:$mime;base64,$thumb";
    }

}
