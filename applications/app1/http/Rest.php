<?php

namespace http {

    use storage\Bucket;
    use storage\Storage;

    class Schema {
        /** @var array */
        private $fields = [];

        /**
         * Schema constructor.
         * @param array $fields
         */
        public function __construct(array $fields)
        {
            foreach ($fields as $name => $required) {
                $this->addField($name, $required);
            }
        }

        public function isValid(array $data) : bool {
            foreach ($this->fields as $name => $required) {
                if (array_key_exists($name, $data)) {
                    unset($data[$name]);
                } elseif ($required) {
                    return false;
                }
            }

            if (count($data) > 0) {
                return false;
            }

            return true;
        }

        public function hydrate(array $fields) : \stdClass
        {
            $entity = new \stdClass();
            foreach ($fields as $name => $value)
            {
                $entity->{$name} = $value;
            }

            return $entity;
        }

        /**
         * @param string $field
         * @param bool|false $required
         */
        public function addField(string $field, bool $required = false)
        {
            $this->fields[$field] = $required;
        }
    }

    class Rest
    {
        /** @var string */
        private $name;
        /** @var Storage */
        private $storage;
        /** @var Schema */
        private $schema;

        /**
         * Rest constructor.
         * @param string $object
         * @param Storage $storage
         */
        public function __construct(string $name, array $object, Bucket $bucket)
        {
            $this->schema = new Schema($object);
            $this->name = $name;
            $this->bucket = $bucket;
        }

        /**
         * @param int|null $id
         * @return array
         */
        public function get(int $id = null) : array
        {
            if (is_null($id)) {
                return [200, $this->bucket->all()];
            }

            if ($this->bucket->has($id)) {
                return [200, $this->bucket->get($id)];
            }

            return [404, []];
        }

        public function post(int $id = null) : array
        {
            $data = $_POST;
            $valid = $this->schema->isValid($data);
            if ($valid) {
                $entity = $this->schema->hydrate($data);
                if ($id) {
                    return [201, $this->bucket->set($id, $entity)];
                }

                return [201, $this->bucket->append($entity)];
            }

            return [404, []];
        }

        public function delete(int $id)
        {
            $this->bucket->delete($id);

            return [204, []];
        }

        /**
         * @return string
         */
        public function name() : string
        {
            return $this->name;
        }
    }
}