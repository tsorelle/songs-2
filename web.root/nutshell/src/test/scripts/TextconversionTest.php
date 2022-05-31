<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use Soundasleep\Html2Text;

class TextconversionTest extends TestScript
{

    public function execute()
    {
        $html = '<h1>Your Monthly Newsletter</h1> '."\n".
            '<p> '."\n".
            '    The  September 2018 issue of our newsletter is ready for you. '."\n".
            '</p> '."\n".
            '<p> '."\n".
            '    <a href="https://www.austinquakers.org/document/201912">View the newsletter</a> on the web. '."\n".
            '</p> '."\n".
            '<p> '."\n".
            '    <a href="https://www.austinquakers.org/document/201912">Download a copy.</a> '."\n".
            '</p> '."\n".
            '<div>Content is here</div> '."\n".
            '<div> '."\n".
            '    This is the footer '."\n".
            '</div> '."\n".
            '<p> '."\n".
            '    Please click here to unsubscribe from this email list: <a href="https://www.austinquakers.org/unsubscribe">un-subscribe</a>'."\n".
            '</p> '."\n";

        $actual = Html2Text::convert($html,['ignore_errors' => true]);
        $this->assertNotEmpty($actual,'text');
        print $actual;
    }
}