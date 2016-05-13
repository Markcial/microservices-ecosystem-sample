<?php

namespace {
    class TypeException extends \Exception
    {
    }
}

namespace storage {
    class Bucket implements \JsonSerializable {
        /** @var string */
        private $name;
        /** @var array */
        private $collection = [];

        /**
         * Bucket constructor.
         * @param string $name
         */
        public function __construct(string $name, array $items = [])
        {
            $this->name = $name;
            $this->collection = $items;
        }

        /**
         * @return string
         */
        public function name() : string
        {
            return $this->name;
        }

        /**
         * @return array
         */
        public function all() : array
        {
            return $this->collection;
        }

        /**
         * @param int $id
         * @param \JsonSerializable $object
         */
        public function set(int $id, \stdClass $object)
        {
            $this->collection[$id] = $object;

            return $object;
        }

        public function append(\stdClass $entity)
        {
            $this->collection[] = $entity;

            end($this->collection);
            $id = key($this->collection);
            reset($this->collection);

            return (object)[$id => $entity];
        }

        /**
         * @param int $id
         * @return bool
         */
        public function has(int $id)
        {
            return array_key_exists($id, $this->collection);
        }

        /**
         * @param int $id
         * @return \JsonSerializable
         */
        public function get(int $id)
        {
            return $this->collection[$id];
        }

        public function delete(int $id) : bool
        {
            unset($this->collection[$id]);

            return true;
        }

        /**
         * Specify data which should be serialized to JSON
         * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
         * @return mixed data which can be serialized by <b>json_encode</b>,
         * which is a value of any type other than a resource.
         * @since 5.4.0
         */
        function jsonSerialize()
        {
            return $this->collection;
        }
    }
    class Storage extends \SplFileObject
    {
        /** @var array */
        private $buckets = [];

        /**
         * Storage constructor.
         * @param $file
         */
        public function __construct(string $file)
        {
            parent::__construct($file, 'rw+');
        }

        public function addBucket(Bucket $bucket)
        {
            $this->buckets[$bucket->name()] = $bucket;
        }

        public function &getBucket(string $name) : Bucket
        {
            if (!array_key_exists($name, $this->buckets)) {
                $this->buckets[$name] = new Bucket($name);
            }

            return $this->buckets[$name];
        }

        /**
         * @return string
         */
        private function read() : string
        {
            $text = "";
            while ( ! $this->eof()) {
                $text .= $this->fgets();
            }

            return $text;
        }

        public function save()
        {
            $json = json_encode($this->buckets);
            if (json_last_error()) {
                throw new \TypeException(sprintf('Unable to convert object to json string, reason : %s', json_last_error_msg()));
            }

            $this->rewind();
            $this->ftruncate(0);
            $this->fwrite($json);
        }

        public function load()
        {
            $object = json_decode($this->read(), true);
            if (json_last_error()) {
                throw new \TypeException(sprintf('Unable to convert string to json object, reason : %s', json_last_error_msg()));
            }

            $this->buckets = $object;

            foreach ($this->buckets as $name => $items) {
                $this->buckets[$name] = new Bucket($name, $items);
            }
        }
    }
}
