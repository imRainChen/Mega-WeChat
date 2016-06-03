<?php
/**
 * Created by PhpStorm.
 * User: rain1
 * Date: 2016/4/14
 * Time: 14:22
 */

namespace Swoole\Util;


class ByteBuffer
{
    private $buffer;
    private $offset;
    private $length;    //实际长度
    private $size;      //buffer大小

    private function __construct($size = 124)
    {
        if (is_int($size)) {
            $this->offset = 0;
            $this->length = 0;
            $this->size = $size;
            $this->buffer = new \swoole_buffer($size);
         } else {
            throw new \InvalidArgumentException('Invalid data parameter\'s type');
        }
    }

    public static function allocate($size = 128)
    {
        return new self($size);
    }

    public function resetData($data)
    {
        if ($this->size < strlen($data)) {
            throw new \InvalidArgumentException('buffer length not enough!');
        }

        $this->offset = 0;
        $this->length = strlen($data);
        $this->buffer->clear();
        $this->buffer->write(0, $data);
    }

    public function position($index)
    {
        $this->offset = $index;
    }

    public function write($data, $len)
    {
        $increment = $this->offset + $len;
        if ($this->size < $increment) {
            throw new \InvalidArgumentException('buffer length not enough!');
        }
        if ($increment > $this->length) {
            $this->length = $increment;
        }
        $this->buffer->write($this->offset, $data);
        $this->offset += $len;
    }

//    public function writeByte($d)
//    {
//        $this->length += 1;
//        $this->buffer->append(pack("C1", $d));
//    }

    public function writeInt($data)
    {
        $this->write(pack("l1", $data), 4);
    }

    public function writeString($data, $len = null)
    {
        $len = $len === null ? strlen($data) : $len;
        $this->write(pack("a*", $data), $len);
    }

//    public function writeString($s, $len = null, $offset = null)
//    {
//        if (null === $len) {
//            $len = strlen($s);
//        }
//        $this->writeInt($len, $offset);
//        if ($offset === null) {
//            $this->buffer->write($this->offset, pack("a*", $s));
//            $this->offset += $len;
//            $this->length += $len;
//        } else {
//            $offset += 4;
//            $this->write($offset, pack("a*", $s), $len);
//            $this->offset = $offset;
//        }
//    }
//
//    public function writeInt($i, $offset = null)
//    {
//        if ($offset === null) {
//            $this->buffer->write($this->offset, pack("N1", $i));
//            $this->offset += 4;
//            $this->length += 4;
//        } else {
//            $this->write($offset, pack("N1", $i), 4);
//            $this->offset = $offset;
//        }
//    }

//    public function writeBinary($b, $len = null, $offset = null)
//    {
//        if (null === $len) {
//            $len = strlen($b);
//        }
//        $this->writeInt($len);
//        if (null === $offset) {
//            $this->write($offset, $b, $len);
//        } else {
//             += $len;
//            $this->buffer->append($b);
//        }
//    }

//    public function writeBool($d, $offset = null)
//    {
//        if (null === $offset) {
//            $this->write($offset, pack("C1", $d), 1);
//        } else {
//             += 1;
//            $this->buffer->append(pack("C1", $d));
//        }
//    }



//    public function writeInt16($i)
//    {
//         += 2;
//        $this->buffer->append(pack("n1", $i));
//    }

    public function append($data)
    {
        $this->offset += strlen($data);
        $this->buffer->append($data);
    }

    public function read($offset, $length)
    {
        $this->offset += $length;
        return $this->buffer->read($offset, $length);
    }

//    public function readByte()
//    {
//        $ret = unpack("C1ele", $this->buffer->read($this->offset, 1));
//        $this->offset += 1;
//        return $ret['ele'];
//    }

    public function readInt()
    {
        $ret = unpack("l1ele", $this->buffer->read($this->offset, 4));
        $this->offset += 4;
        return $ret['ele'];
    }

//    public function readInt16()
//    {
//        $ret = unpack("n1ele", $this->buffer->read($this->offset, 2));
//        $this->offset += 2;
//        return $ret['ele'];
//    }

    public function readString($len = null)
    {
        if ($len == null) {
            $len = $this->readInt();
        }
        $ret = unpack("a*ele", $this->buffer->read($this->offset, $len));
        $this->offset += $len;
        return $ret['ele'];
    }

    //读二进制
//    public function readBinary()
//    {
//        $len = $this->readInt();
//        $ret = $this->buffer->read($this->offset, $len);
//        $this->offset += $len;
//        return $ret;
//    }
//
//    public function readBool()
//    {
//        $ret = unpack("C1ele", $this->buffer->read($this->offset, 1));
//        $this->offset += 1;
//        return $ret['ele'];
//    }

    public function getData()
    {
        return $this->buffer->read(0, $this->length);
    }

    public function getBuffer()
    {
        if ($this->offset < $this->length) {
            return $this->buffer->read($this->offset, $this->length);
        }
        return null;
    }

    public function clear()
    {
        $this->buffer->clear();
    }

    public function isEnd()
    {
        return $this->offset >= $this->length;
    }
}


// 测试代码
//$buffer = \Swoole\Util\ByteBuffer::allocate(10000);
//$buffer->writeInt(4);       //4
//$buffer->writeInt(5);       //8
//$buffer->position(8);
//$buffer->writeInt(5);
//$buffer->writeString("aaaaa", 5);  //13
//$buffer->position(17);
//$buffer->writeInt(5);
//$buffer->writeString("bbbbb", 5);  //18
//$buffer->writeInt(1);
//$buffer->writeString("3", 1);  //23
//$buffer->position(4);
//$buffer->writeInt(6);
//
//$buffer->position(0);
//echo $buffer->readInt() . PHP_EOL;
//echo $buffer->readInt() . PHP_EOL;
//$len = $buffer->readInt();
//echo $buffer->readString($len). PHP_EOL;
//$len = $buffer->readInt();
//echo $buffer->readString($len). PHP_EOL;
//echo $buffer->readString(). PHP_EOL;