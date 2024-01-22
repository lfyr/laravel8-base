#创建脚本
`php artisan make:command commandName`  
#执行脚本
`php artisan command:consumer_mq`

#消息日志表
CREATE TABLE `mq_log` (
`id` int unsigned NOT NULL AUTO_INCREMENT,
`mq_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
`mq_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
`status` tinyint NOT NULL DEFAULT '0' COMMENT '0-初始 1-投递成功 2-消费成功 3-死信',
`mq_body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '消息体',
`retry_deliver_num` tinyint NOT NULL DEFAULT '0' COMMENT '重试次数',
`consume_err_num` tinyint NOT NULL DEFAULT '0' COMMENT '消费失败次数',
`created_at` datetime DEFAULT NULL,
`updated_at` datetime DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
