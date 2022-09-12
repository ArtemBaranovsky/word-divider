<?php

namespace Classes;

class Captcha extends Notification
{
    /**
     * @var string
     */
    public string $confirmName = 'code';

    /**
     * @var string
     */
    public static $img;


    public function send(string $to, string $subject, string $message): bool
    {
        return true;
    }

    /**
     * @param string $answer
     * @return bool
     */
    public function check(string $answer): bool
    {
        if(!empty($answer) && $_SESSION['digit']) {

            if ($answer != $_SESSION['digit']) {
                $_SESSION['error'][] = "Sorry, the CAPTCHA code entered was incorrect!";
                return false;
            }
        }

        return true;
    }

    public function html()
    {
        $this->makeCaptchaImg();

        echo
        '<div class="form-group">
            <label for="captcha">Captcha</label>
            <img src="'. self::$img.'" alt="" name="captcha">
        </div>
        <div class="form-group">
            <label for="code">Enter Captcha</label>
            <input type="text" class="form-control"  placeholder="Enter captcha code" size="6" maxlength="5" name="code" value="" >
        </div>';
    }

    protected function makeCaptchaImg(): void
    {
        session_start();

        $image = @imagecreatetruecolor(120, 30) or die("Cannot Initialize new GD image stream");

        $background = imagecolorallocate($image, 0x66, 0x99, 0x66);
        imagefill($image, 0, 0, $background);
        $linecolor = imagecolorallocate($image, 0x99, 0xCC, 0x99);
        $textcolor1 = imagecolorallocate($image, 0x00, 0x00, 0x00);
        $textcolor2 = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);

        for ($i = 0; $i < 6; $i++) {
            imagesetthickness($image, rand(1, 3));
            imageline($image, 0, rand(0, 30), 120, rand(0, 30), $linecolor);
        }

        $digit = '';
        for ($x = 15; $x <= 95; $x += 20) {
            $textcolor = (rand() % 2) ? $textcolor1 : $textcolor2;
            $digit .= ($num = rand(0, 9));
            imagechar($image, rand(3, 5), $x, rand(2, 14), $num, $textcolor);
        }

        $_SESSION['digit'] = $digit;

        ob_start();
        imagepng($image);

        imagedestroy($image);
        $img64 = base64_encode(ob_get_clean());
        self::$img = 'data:image/png;base64,' . $img64;
    }
}