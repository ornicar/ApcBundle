<?php

namespace Ornicar\ApcBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester,
    Symfony\Component\Console\Application;

use Ornicar\ApcBundle\Command\ApcClearCommand;

/**
 * Class ApcClearCommandTest
 * @author Thierry Marianne <thierry.marianne@weaving-the-web.org>
 */
class ApcClearCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $application = new Application();
        $clearCacheCommand = new ApcClearCommand();
        $clearCacheCommand->setClearMode('goutte');

        $clientMockBuilder = $this->getMockBuilder('\Goutte\Client');
        $clientMock = $clientMockBuilder->disableOriginalConstructor()
            ->setMethods(array('request', 'getResponse'))
            ->getMock();

        $responseMockBuilder = $this->getMockBuilder('\Symfony\Component\BrowserKit\Response');
        $responseMock = $responseMockBuilder->disableOriginalConstructor()
            ->setMethods(array('getContent', 'getStatus'))
            ->getMock();

        $successMessage = 'cache successfully cleared up';
        $responseMock->expects($this->once())->method('getContent')->will($this->returnValue(json_encode(array(
            'message' => $successMessage,  'success' => true))));
        $responseMock->expects($this->once())->method('getStatus')->will($this->returnValue(200));

        $clientMock->expects($this->once())->method('getResponse')->will($this->returnValue($responseMock));
        $clearCacheCommand->setClient($clientMock);

        $webDir = __DIR__;
        $clearCacheCommand->setWebDir($webDir);

        $application->add($clearCacheCommand);

        $command = $application->find('apc:clear');
        $commandTester = new CommandTester($command);

        $commandTester->execute(array('command' => $command->getName(),
            '--mode' => 'goutte',
            '--host' => 'http://127.0.0.1',
            '--web_dir' => __DIR__));

        $this->assertContains($successMessage, $commandTester->getDisplay());
        $this->assertFileNotExists($command->filePath);
    }
}