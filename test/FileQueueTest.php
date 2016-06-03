<?php
include dirname(__DIR__) . "/autoload.php";
$path = __DIR__ . '/queue';
$queue = new \Swoole\Queue\FileQueue($path);

////队列基础测试
//$command = new \Network\SendCommand(1, 'adsfdasfdff', ['a' => 'aaasa']);
//$queue->push(serialize($command));
//assertEquals(serialize($command), $queue->pop());
//$queue->push("test1");
//$queue->push("test2");
//assertEquals($queue->pop(), 'test1');
//$queue->push("test3");
//$queue->push("test4");
//assertEquals($queue->pop(), 'test2');
//assertEquals($queue->pop(), 'test3');
//echo $queue->pop() . PHP_EOL;
//assertEquals(0, $queue->count());
//$queue->clear();
//if ($queue->pop() === null) {
//    echo "null true" . PHP_EOL;
//}

// 入队和取队测试，并检查是否自动翻滚队列文件和删除文件
$str = str_repeat('a', 1024);
for ($i = 0; $i < 100000; $i++)
{
    $queue->push($str . $i);
}

assertEquals(100000, $queue->count());
//
//for ($i = 0; $i < 100000; $i++)
//{
//    $b = $queue->pop();
//    if ($b === null) {
//        $i--;
//        echo "null" . $i . PHP_EOL;
//        continue;
//    }
//    echo ($i + 1) . ' ';
//    assertEquals($b, ($str . $i));
//}

// 性能测试
//$start = microtime(true);
//for ($i = 0; $i < 10000000; $i++)
//{
//    $queue->push('1234567890');
//    //echo 'go';
//}
//echo "写入10字节10000000次：" . (microtime(true) - $start);
//$queue->clear();


function assertEquals($a, $b){
    //echo "{$a} - {$b}. ";
    if ($a === $b) {
        echo 'true' . PHP_EOL;
        return true;
    }
    echo 'false' . PHP_EOL;
    return false;
}

$queue->close();
