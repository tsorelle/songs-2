<?php 
/** 
 * Created by /tools/create-model.php 
 * Time:  2017-11-15 13:11:28
 */ 
namespace Peanut\contacts\db\model\repository;


use \PDO;
use PDOStatement;
use Tops\db\TDatabase;
use \Tops\db\TEntityRepository;

class EmailMessageQueueRepository extends \Tops\db\TEntityRepository
{
    // todo: adapt from FMA code if neeted
    protected function getTableName() {
        return 'qnut_email_message_recipients';
    }
        protected function getDatabaseId() {
            return null;
        }

        protected function getClassName() {
            return 'Peanut\contacts\db\model\entity\EmailMessageRecipient';
        }

        protected function getFieldDefinitionList()
        {
            return array(
            'id'=>PDO::PARAM_INT,
            'mailMessageId'=>PDO::PARAM_INT,
            'personId'=>PDO::PARAM_STR);
        }
        public function queueMessages($messageId,$listId) {
/*            $sql = 'INSERT INTO qnut_email_message_queue (mailMessageId,personId) '.
                'SELECT $messageId AS mailMessageId, p.uid AS personId '.
                'FROM qnut_email_subscriptions s '.
                'JOIN pnut_contacts p ON s.personId = p.id '.
                'WHERE s.listId = ?';
            $stmt = $this->executeStatement($sql, [$listId]);
            return $stmt->rowCount();*/
        }
}