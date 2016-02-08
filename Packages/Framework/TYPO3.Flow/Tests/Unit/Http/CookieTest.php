<?php
namespace TYPO3\Flow\Tests\Unit\Http;

/*
 * This file is part of the TYPO3.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Flow\Http\Uri;
use TYPO3\Flow\Http\Cookie;

/**
 * Test case for the Http Cookie class
 */
class CookieTest extends \TYPO3\Flow\Tests\UnitTestCase
{
    /**
     * @return array
     */
    public function invalidCookieNames()
    {
        return array(
            array('foo bar'),
            array('foo(bar)'),
            array('<foo>'),
            array('@foo'),
            array('foo[bar]'),
            array('foo:bar'),
            array('foo;'),
            array('foo?'),
            array('foo{bar}'),
            array('"foo"'),
            array('foo/bar'),
            array('föö'),
            array('„foo“'),
        );
    }

    /**
     * @return array
     */
    public function validCookieNames()
    {
        return array(
            array('foo'),
            array('foo_bar'),
            array('foo\'bar'),
            array('foo*bar'),
            array('MyNameIsFooAndYoursIsBar1234567890'),
            array('foo|bar'),
            array('$foo%bar~baz'),
        );
    }

    /**
     * @param string  $cookieName
     * @test
     * @dataProvider invalidCookieNames
     * @expectedException \InvalidArgumentException
     */
    public function constructorThrowsExceptionOnInvalidCookieNames($cookieName)
    {
        new Cookie($cookieName);
    }

    /**
     * @param string  $cookieName
     * @test
     * @dataProvider validCookieNames
     */
    public function constructorAcceptsValidCookieNames($cookieName)
    {
        $cookie = new Cookie($cookieName);
        $this->assertEquals($cookieName, $cookie->getName());
    }

    /**
     * @test
     */
    public function getValueReturnsTheSetValue()
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertEquals('bar', $cookie->getValue());

        $cookie = new Cookie('foo', 'bar');
        $cookie->setValue('baz');
        $this->assertEquals('baz', $cookie->getValue());

        $cookie = new Cookie('foo', true);
        $this->assertSame(true, $cookie->getValue());

        $uri = new Uri('http://localhost');
        $cookie = new Cookie('foo', $uri);
        $this->assertSame($uri, $cookie->getValue());
    }

    /**
     * @return array
     */
    public function invalidExpiresParameters()
    {
        return array(
            array('foo'),
            array('-1'),
            array(new \stdClass()),
            array(false)
        );
    }

    /**
     * @param mixed $parameter
     * @test
     * @dataProvider invalidExpiresParameters
     * @expectedException \InvalidArgumentException
     */
    public function constructorThrowsExceptionOnInvalidExpiresParameter($parameter)
    {
        new Cookie('foo', 'bar', $parameter);
    }

    /**
     * @test
     */
    public function getExpiresAlwaysReturnsAUnixTimestamp()
    {
        $cookie = new Cookie('foo', 'bar', 1345110803);
        $this->assertSame(1345110803, $cookie->getExpires());

        $cookie = new Cookie('foo', 'bar', \DateTime::createFromFormat('U', 1345110803));
        $this->assertSame(1345110803, $cookie->getExpires());

        $cookie = new Cookie('foo', 'bar');
        $this->assertSame(0, $cookie->getExpires());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function constructorThrowsExceptionOnInvalidMaximumAgeParameter()
    {
        new Cookie('foo', 'bar', 0, 'urks');
    }

    /**
     * @test
     */
    public function getMaximumAgeReturnsTheMaximumAge()
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertSame(null, $cookie->getMaximumAge());

        $cookie = new Cookie('foo', 'bar', 0, 120);
        $this->assertSame(120, $cookie->getMaximumAge());
    }

    /**
     * @return array
     */
    public function invalidDomains()
    {
        return array(
            array(' me.com'),
            array('you .com'),
            array('-typo3.org'),
            array('typo3.org.'),
            array('.typo3.org'),
            array(false)
        );
    }

    /**
     * @param mixed $domain
     * @test
     * @dataProvider invalidDomains
     * @expectedException \InvalidArgumentException
     */
    public function constructorThrowsExceptionOnInvalidDomain($domain)
    {
        new Cookie('foo', 'bar', 0, null, $domain);
    }

    /**
     * @test
     */
    public function getDomainReturnsDomain()
    {
        $cookie = new Cookie('foo', 'bar', 0, null, 'flow.typo3.org');
        $this->assertSame('flow.typo3.org', $cookie->getDomain());
    }

    /**
     * @return array
     */
    public function invalidPaths()
    {
        return array(
            array('/foo;'),
            array('/föö/bäär'),
            array("\tfoo"),
            array(false)
        );
    }

    /**
     * @param mixed $path
     * @test
     * @dataProvider invalidPaths
     * @expectedException \InvalidArgumentException
     */
    public function constructorThrowsExceptionOnInvalidPath($path)
    {
        new Cookie('foo', 'bar', 0, null, null, $path);
    }

    /**
     * @test
     */
    public function getPathReturnsPath()
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertSame('/', $cookie->getPath());

        $cookie = new Cookie('foo', 'bar', 0, null, 'flow.typo3.org', '/about/us');
        $this->assertSame('/about/us', $cookie->getPath());
    }

    /**
     * @test
     */
    public function isSecureReturnsSecureFlag()
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertFalse($cookie->isSecure());

        $cookie = new Cookie('foo', 'bar', 0, null, 'typo3.org', '/', true);
        $this->assertTrue($cookie->isSecure());
    }

    /**
     * @test
     */
    public function isHttpOnlyReturnsHttpOnlyFlag()
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertTrue($cookie->isHttpOnly());

        $cookie = new Cookie('foo', 'bar', 0, null, 'typo3.org', '/', false, false);
        $this->assertFalse($cookie->isHttpOnly());
    }

    /**
     * @test
     */
    public function isExpiredTellsIfTheCookieIsExpired()
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertFalse($cookie->isExpired());

        $cookie->expire();
        $this->assertTrue($cookie->isExpired());

        $cookie = new Cookie('foo', 'bar', 500);
        $this->assertTrue($cookie->isExpired());
    }

    /**
     * Data provider with cookies and their expected string representation.
     *
     * @return array
     */
    public function cookiesAndTheirStringRepresentations()
    {
        $expiredCookie = new Cookie('foo', 'bar');
        $expiredCookie->expire();

        return array(
            array(new Cookie('foo', 'bar'), 'foo=bar; Path=/; HttpOnly'),
            array(new Cookie('MyFoo25', 'bar'), 'MyFoo25=bar; Path=/; HttpOnly'),
            array(new Cookie('MyFoo25', true), 'MyFoo25=1; Path=/; HttpOnly'),
            array(new Cookie('MyFoo25', false), 'MyFoo25=0; Path=/; HttpOnly'),
            array(new Cookie('foo', 'bar', 0), 'foo=bar; Path=/; HttpOnly'),
            array(new Cookie('MyFoo25'), 'MyFoo25=; Path=/; HttpOnly'),
            array(new Cookie('foo', 'It\'s raining cats and dogs.'), 'foo=It%27s+raining+cats+and+dogs.; Path=/; HttpOnly'),
            array(new Cookie('foo', 'Some characters, like "double quotes" must be escaped.'), 'foo=Some+characters%2C+like+%22double+quotes%22+must+be+escaped.; Path=/; HttpOnly'),
            array(new Cookie('foo', 'bar', 1345108546), 'foo=bar; Expires=Thu, 16-Aug-2012 09:15:46 GMT; Path=/; HttpOnly'),
            array(new Cookie('foo', 'bar', \DateTime::createFromFormat('U', 1345108546)), 'foo=bar; Expires=Thu, 16-Aug-2012 09:15:46 GMT; Path=/; HttpOnly'),
            array(new Cookie('foo', 'bar', 0, null, 'flow.typo3.org'), 'foo=bar; Domain=flow.typo3.org; Path=/; HttpOnly'),
            array(new Cookie('foo', 'bar', 0, null, 'flow.typo3.org', '/about'), 'foo=bar; Domain=flow.typo3.org; Path=/about; HttpOnly'),
            array(new Cookie('foo', 'bar', 0, null, 'typo3.org', '/', true), 'foo=bar; Domain=typo3.org; Path=/; Secure; HttpOnly'),
            array(new Cookie('foo', 'bar', 0, null, 'typo3.org', '/', true, false), 'foo=bar; Domain=typo3.org; Path=/; Secure'),
            array(new Cookie('foo', 'bar', 0, 3600), 'foo=bar; Max-Age=3600; Path=/; HttpOnly'),
            array($expiredCookie, 'foo=bar; Expires=Thu, 27-May-1976 12:00:00 GMT; Path=/; HttpOnly')
        );
    }

    /**
     * Checks if the Cookie cast to a string equals the expected string which can
     * be used as a value for the Set-Cookie header.
     *
     * @param \TYPO3\Flow\Http\Cookie $cookie
     * @param string $expectedString
     * @return void
     * @test
     * @dataProvider cookiesAndTheirStringRepresentations()
     */
    public function stringRepresentationOfCookieIsValidSetCookieFieldValue(Cookie $cookie, $expectedString)
    {
        $this->assertEquals($expectedString, (string)$cookie);
    }

    /**
     * @test
     */
    public function createCookieFromRawReturnsNullIfBasicNameOrValueAreNotSatisfied()
    {
        $this->assertNull(Cookie::createFromRawSetCookieHeader('Foobar'), 'The cookie without a = char at all is not discarded.');
        $this->assertNull(Cookie::createFromRawSetCookieHeader('=Foobar'), 'The cookie with only a leading = char, hence without a name, is not discarded.');
    }

    /**
     * @test
     */
    public function createCookieFromRawDoesntCareAboutUnkownAttributeValues()
    {
        $cookie = Cookie::createFromRawSetCookieHeader('ckName=someValue; someproperty=itsvalue');
        $this->assertEquals('ckName', $cookie->getName());
        $this->assertEquals('someValue', $cookie->getValue());
    }

    /**
     * @test
     */
    public function createCookieFromRawParsesExpiryDateCorrectly()
    {
        $cookie = Cookie::createFromRawSetCookieHeader('ckName=someValue; Expires=Sun, 16-Oct-2022 17:53:36 GMT');
        $this->assertSame(1665942816, $cookie->getExpires());
    }

    /**
     * @test
     */
    public function createCookieFromRawAssumesExpiryDateZeroIfItCannotBeParsed()
    {
        $cookie = Cookie::createFromRawSetCookieHeader('ckName=someValue; Expires=trythis');
        $this->assertSame(0, $cookie->getExpires());
    }

    /**
     * @test
     */
    public function createCookieFromRawParsesMaxAgeCorrectly()
    {
        $cookie = Cookie::createFromRawSetCookieHeader('ckName=someValue; Max-Age=-20');
        $this->assertSame(-20, $cookie->getMaximumAge());
    }

    /**
     * @test
     */
    public function createCookieFromRawIgnoresMaxAgeIfInvalid()
    {
        $cookie = Cookie::createFromRawSetCookieHeader('ckName=someValue; Max-Age=--foo');
        $this->assertNull($cookie->getMaximumAge());
    }

    /**
     * @test
     */
    public function createCookieFromRawIgnoresDomainAttributeIfValueIsEmpty()
    {
        $cookie = Cookie::createFromRawSetCookieHeader('ckName=someValue; Domain=; more=nothing');
        $this->assertNull($cookie->getDomain());
    }

    /**
     * @test
     */
    public function createCookieFromRawRemovesLeadingDotForDomainIfPresent()
    {
        $cookie = Cookie::createFromRawSetCookieHeader('ckName=someValue; Domain=.example.org');
        $this->assertEquals('example.org', $cookie->getDomain());
    }

    /**
     * @test
     */
    public function createCookieFromRawLowerCasesDomainName()
    {
        $cookie = Cookie::createFromRawSetCookieHeader('ckName=someValue; Domain=EXample.org');
        $this->assertEquals('example.org', $cookie->getDomain());
    }

    /**
     * @test
     */
    public function createCookieFromRawAssumesDefaultPathIfNoLeadingSlashIsPresent()
    {
        $cookie = Cookie::createFromRawSetCookieHeader('ckName=someValue; Path=foo');
        $this->assertEquals('/', $cookie->getPath());
    }

    /**
     * @test
     */
    public function createCookieFromRawUsesPathCorrectly()
    {
        $cookie = Cookie::createFromRawSetCookieHeader('ckName=someValue; Path=/foo');
        $this->assertEquals('/foo', $cookie->getPath());
    }

    /**
     * @test
     */
    public function createCookieFromRawSetsSecureIfPresent()
    {
        $cookie = Cookie::createFromRawSetCookieHeader('ckName=someValue; Secure; more=nothing');
        $this->assertTrue($cookie->isSecure());
    }

    /**
     * @test
     */
    public function createCookieFromRawSetsHttpOnlyIfPresent()
    {
        $cookie = Cookie::createFromRawSetCookieHeader('ckName=someValue; HttpOnly; more=nothing');
        $this->assertTrue($cookie->isHttpOnly());
    }
}
