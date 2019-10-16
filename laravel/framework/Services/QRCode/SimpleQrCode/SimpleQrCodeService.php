<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/7/12
 * Time: 20:00
 */
namespace Framework\Services\QRCode\SimpleQrCode;

class SimpleQrCodeService
{
    /**
     * 自己定义输出图片格式，支持 PNG，EPS，SVG 三种格式，默认输出SVG格式的图片
     * @var string $format
     */
    private $format = 'svg';
    /**
     * 尺寸设置，默认返回可能最小像素单位的二维码
     * @var int $size
     */
    private $size;
    /**
     * 颜色设置，颜色设置的格式必须是RBG格式，设置方式如下：
     *      $color = [
     *          'red'   => 255,
     *          'green' => 0,
     *          'blue'  => 255,
     *      ];
     * @var array $color
     */
    private $color;
    /**
     * 背景色设置，参照颜色设置
     * @var array $backgroundColor
     */
    private $backgroundColor;
    /**
     * 边距设置
     * @var int $margin
     */
    private $margin;
    /**
     * 容错级别设置，容错级别越高,二维码里能存储的数据越少，需要使用大写字母。详情见：http://en.wikipedia.org/wiki/QR_code#Error_correction
     * 推荐使用 H 级别。容错级别说明如下：
     *      L：   7%  的字节码恢复率
     *      M：   15% 的字节码恢复率
     *      Q：   25% 的字节码恢复率
     *      H：   30% 的字节码恢复率
     * @var string $errorCorrection
     */
    private $errorCorrection = 'H';
    /**
     * 编码设置，默认使用 ISO-8859-1， 详情见：http://en.wikipedia.org/wiki/Character_encoding
     * 若抛出 Could not encode content to ISO-8859-1 意味着使用了错误的编码. 建议使用 UTF-8.
     * @var string $encoding
     */
    private $encoding = 'UTF-8';
    /**
     * LOGO图设置，设置方式如下：
     *      $merge = [
     *          'filePath'   => 'path-to-image.png',    // LOGO文件路径
     *          'percentage' => 0.2,    // LOGO图片占整个二维码图片的百分比
     *          'absolute'   => false   // 是否使用绝对路径
     *      ];
     * @var array $merge
     */
    private $merge;
    /**
     * 二进制LOGO图片设置，设置方式如下：
     *      $mergeString = [
     *          'content'    => Storage::get('path/to/image.png'),   // LOGO图片内容
     *          'percentage' => 0.2     // LOGO图片占整个二维码图片的百分比
     *      ];
     * @var array $mergeString
     */
    private $mergeString;

    /**
     * 生成二维码
     * @author Sojo
     * @param string $content
     *      使用场景                            前缀                      例子
     *      网址                              http://        http://www.simplesoftware.io
     *      加密网址                          https://       https://www.simplesoftware.io
     *      E-mail地址                        mailto:        mailto:support@simplesoftware.io
     *      电话号码                            tel:          tel:555-555-5555
     *      文字短信                            sms:          sms:555-555-5555
     *      文字短信内容                         sms:          sms::I am a pretyped message
     *      文字短信同时附带手机号和短信内容         sms:          sms:555-555-5555:I am a pretyped message
     *      坐标                                geo:          geo:-78.400364,-85.916993
     *      MeCard名片                        mecard:         MECARD:Simple, Software;Some Address, Somewhere, 20430;TEL:555-555-5555;EMAIL:support@simplesoftware.io;
     *      VCard名片                       BEGIN:VCARD       See Examples：https://en.wikipedia.org/wiki/VCard
     *      Wifi                              wifi:          wifi:WEP/WPA;SSID;PSK;Hidden(True/False)
     * @param null|string $filePath
     * @return string|void
     */
    public function generate($content, $filePath = null)
    {
        $qrCode = $this->loadConfiguration();
        return $qrCode->generate($content, $filePath);
    }

    /**
     * 生成一个直接发E-mail的二维码，包含了发邮件的地址、标题和内容
     * @author Sojo
     * @param null|string $to
     * @param null|string $subject
     * @param null|string $body
     * @return mixed
     */
    public function generateEmail($to, $subject = null, $body = null)
    {
        $qrCode = $this->loadConfiguration();
        return $qrCode->email($to, $subject, $body);
    }

    /**
     * 生成一个包含一个经纬度的位置二维码
     * @author Sojo
     * @param float $latitude
     * @param float $longitude
     * @return mixed
     */
    public function generateGeo($latitude, $longitude)
    {
        $qrCode = $this->loadConfiguration();
        return $qrCode->geo($latitude, $longitude);
    }

    /**
     * 生成一个包含自己手机号的二维码
     * @author Sojo
     * @param string $phoneNumber
     * @return mixed
     */
    public function generatePhoneNumber($phoneNumber)
    {
        $qrCode = $this->loadConfiguration();
        return $qrCode->phoneNumber($phoneNumber);
    }

    /**
     * 生成一个包含发送短信目标手机号和内容的二维码
     * @author Sojo
     * @param string $phoneNumber
     * @param string $message
     * @return mixed
     */
    public function generateSMS($phoneNumber, $message)
    {
        $qrCode = $this->loadConfiguration();
        return $qrCode->SMS($phoneNumber, $message);
    }

    /**
     * 生成一个扫一下就能连接WIFI的二维码
     * @author Sojo
     * @param string $ssid 网络的SSID
     * @param string $password 网络的密码
     * @param bool $hidden 是否是一个隐藏SSID的网络
     * @param string $encryption WPA/WEP
     * @return mixed
     */
    public function generateWiFi($ssid, $password, $hidden, $encryption)
    {
        $qrCode = $this->loadConfiguration();
        return $qrCode->wiFi([
            'ssid'       => $ssid,
            'password'   => $password,
            'hidden'     => $hidden,
            'encryption' => $encryption
        ]);
    }

    /**
     * 加载配置
     * @author Sojo
     * @return $this
     */
    private function loadConfiguration()
    {
        // 当设置LOGO图片时，容错级别设置为 H 级别，图片格式设置为 PNG 格式
        if (isset($this->merge) || isset($this->mergeString)) {
            $this->format = 'png';
            $this->errorCorrection = 'H';
        }

        // 设置图片格式、容错级别以及编码
        $qrCode = \QrCode::format($this->format)->errorCorrection($this->errorCorrection)->encoding($this->encoding);

        // 设置尺寸
        if (isset($this->size)) $qrCode = $qrCode->size($this->size);
        // 设置颜色
        if (isset($this->color)) {
            $red = isset($this->color['red']) ? $this->color['red'] : 0;
            $green = isset($this->color['green']) ? $this->color['green'] : 0;
            $blue = isset($this->color['blue']) ? $this->color['blue'] : 0;
            $qrCode = $qrCode->color((int)$red, (int)$green, (int)$blue);
        }
        // 设置背景色
        if (isset($this->backgroundColor)) {
            $red = isset($this->backgroundColor['red']) ? $this->backgroundColor['red'] : 0;
            $green = isset($this->backgroundColor['green']) ? $this->backgroundColor['green'] : 0;
            $blue = isset($this->backgroundColor['blue']) ? $this->backgroundColor['blue'] : 0;
            $qrCode = $qrCode->backgroundColor((int)$red, (int)$green, (int)$blue);
        }
        // 设置边距
        if (isset($this->margin)) $qrCode = $qrCode->margin($this->margin);
        // 设置LOGO图片
        if (isset($this->merge)) $qrCode = $qrCode->merge($this->merge['filePath'], $this->merge['percentage'], $this->merge['absolute']);
        // 设置二进制LOGO图片
        if (isset($this->mergeString)) $qrCode = $qrCode->mergeString($this->mergeString['content'], $this->mergeString['percentage']);

        return $qrCode;
    }

    /**
     * 设置图片格式
     * @author Sojo
     * @param string $format
     * @return $this
     */
    public function setFormat($format)
    {
        $pictureFormat = ['png', 'eps', 'svg'];
        if (!in_array($format, $pictureFormat)) $format = 'png';
        $this->format = $format;

        return $this;
    }

    /**
     * 设置尺寸
     * @author Sojo
     * @param int $pixels
     * @return $this
     */
    public function setSize($pixels)
    {
        $this->size = (int)$pixels;

        return $this;
    }

    /**
     * 设置颜色
     * @author Sojo
     * @param int $red
     * @param int $green
     * @param int $blue
     * @return $this
     */
    public function setColor($red, $green, $blue)
    {
        $this->color = [
            'red'   => (int)$red,
            'green' => (int)$green,
            'blue'  => (int)$blue
        ];

        return $this;
    }

    /**
     * 设置背景色
     * @author Sojo
     * @param int $red
     * @param int $green
     * @param int $blue
     * @return $this
     */
    public function setBackgroundColor($red, $green, $blue)
    {
        $this->backgroundColor = [
            'red'   => (int)$red,
            'green' => (int)$green,
            'blue'  => (int)$blue
        ];

        return $this;
    }

    /**
     * 设置边距
     * @author Sojo
     * @param int $margin
     * @return $this
     */
    public function setMargin($margin)
    {
        $this->margin = (int)$margin;

        return $this;
    }

    /**
     * 设置容错级别
     * @author Sojo
     * @param string $level
     * @return $this
     */
    public function setErrorCorrection($level)
    {
        $correctionLevel = ['L', 'M', 'Q', 'H'];
        if (!in_array($level, $correctionLevel)) $level = 'H';
        $this->errorCorrection = $level;

        return $this;
    }

    /**
     * 设置编码
     * @author Sojo
     * @param string $encoding
     * @return $this
     */
    public function setEncoding($encoding)
    {
        $encodingType = [
            'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5',
            'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10',
            'ISO-8859-11', 'ISO-8859-12', 'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15',
            'ISO-8859-16', 'SHIFT-JIS', 'WINDOWS-1250', 'WINDOWS-1251', 'WINDOWS-1252',
            'WINDOWS-1256', 'UTF-16BE', 'UTF-8', 'ASCII', 'GBK', 'EUC-KR'
        ];
        if (!in_array($encoding, $encodingType)) $encoding = 'UTF-8';
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * 设置LOGO图
     * @author Sojo
     * @param string $filePath
     * @param float $percentage
     * @param bool $absolute
     * @return $this
     */
    public function setMerge($filePath, $percentage = 0.2, $absolute = false)
    {
        $this->merge = [
            'filePath'   => $filePath,
            'percentage' => $percentage,
            'absolute'   => $absolute
        ];

        return $this;
    }

    /**
     * 设置二进制LOGO图片
     * @author Sojo
     * @param string $content
     * @param float $percentage
     * @return $this
     */
    public function setMergeString($content, $percentage = 0.2)
    {
        $this->mergeString = [
            'content'    => $content,
            'percentage' => $percentage,
        ];

        return $this;
    }
}