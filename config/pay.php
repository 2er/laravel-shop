<?php
/**
 * Created by PhpStorm.
 * User: wuchuanchuan
 * Date: 2018/9/25
 * Time: 下午1:55
 */

return [
    'alipay' => [
        'app_id'         => '2016092200570381',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtypsKRT37v7i+sQcthwm8MgNOJVaBj/B78Y+vSAHiCkZ/QsSGfaMYCnVsC45CREYUU3jMdqRhWDcVG0QV1aFY57tvCFmsO8FaD/mOOzOMff0ohM6ikANO27BzUf/2pMlOXrzrzbHgZ0XMcipypBJ9FDCFMQbAYRGnUOrvI15BbV76G3dJWprunC5WcQlk2q8vYjqlNNakxIGjZud1Y7NtTYirzn7UvTse4WZi9jKLwogflwv3GeeQlKUBCOv7DlGOuS9atjaYhFir+jn8GMKmWEXgC7XWxTSmq7gomuGUb6BQmYiiwRqqR9Cx7+9UlAragr2eRUxWV6l2KRYFbTSZQIDAQAB',
        'private_key'    => 'MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQDfj3GSth3pg2ima3YXBesEgTyaAUOtzM9+vWsq/w/Szs8WTC+OZPuc3i+m5in0WR8TQnw7t1GYezhMUj6wlSOvqOGG3eswfsfqWZRlQKQ+e6UaL8ut+ZPA2SOfxwA9TdoBfXT9HG2GUwicnSzadqJN/sr80mUIliKH44akPQjPIKjGGikl5ERfOWpm+ariNH+rMOF24vpwhP/6zBibZt7bE4G7+4gYLkF/djB1tFAAor5EZaD+BwpH2WupZ14OKTHdZ4fRzoFB7ujMQq4ujk00DxAtjER+UKPigCJAqMDupXF7LqYVsB8C1XGw4g9AEuaI2Jm9ScERO2eqYnrckgb/AgMBAAECggEAQJBdvYApDVptK7rXRPjpkch9JwD8ecIvnqu3upglCr29YvQnu9kALaKhYnMQZMgkSFyoIlrkDsRvUuNhxX+c6cs9LsUCemPy+oRabg+IH5935CMvkJQGjuhm+Grxb8L/FfUUh1DhdKygTRK7dHBUmW/fUbq1gRBS8qnMJbyWwbJjq3vRdq+5ogNXRA7uNNHEuczBcrWIMpFJsZXy33DSfHTziRp8HaEHZNDrZTffTIJzs8VGOodNQOxVXbKc7IRJLrZ3/NOOaxR9PJI6elgMC7hR96uRK/XQUYiVIOUTwefungMzK6zPY87bFZVL0GSaYDVOxB1JKnv13Kmxj4CYIQKBgQD277aWMnakcqSI7pRwBv8OyYRPeT3ojKftgbKoJV694k3Ic9XObBqKvbRD1eBBIS2kC0q/cBJacFxE6tT/7SmJRtf7q2Zhy3GK2YM1C2h61BxYpwnskQhX/VGbpR/auJiEuGWdqbdXcilq8tFEKVfsmoJRw6SBgVMbjvtOHchDkQKBgQDnxBUIAjIYEAFvEJ+9AOQ9NLOZqsX+GcilF8KVu4Rj00X5XZlJFUcOlL1q23yDWcJ+S92K0TK7Y73vcAdQynVuMqo81OTQBKHNAayJX7VgtVgYi4iRQUbi0sLaSMg0OjGLlG9zNV526rV7cwieUrYD+DcM/2So1dxBOVdfsZk5jwKBgCRtkH3vy9jOoL8ikvtDzPSdfctLk3pu5YcADx8HBEb2z4q2q9/byr6U5bbsIjz5bAY7NDexjMOSt8ni6rEmh24c6VfTHTNoE8Pr+pMkr7EPAW+ClNA1RQnV8OgJH/3gJX/OxA7SLp3T1ZBVadGoV7QrxvXQ6r/AGEaEfUY01opBAoGAOvEAFEBSsxHm+3Lz3OJMKVCs2Ei4/61Y/Lt1LhU5TxXc9tUZ9Z8mqgujvHhDSzhZoTr0Bai8STHNjQYlpiNHCBZ0evH9mvWCnJtvYKRpgUW9OMjLm31JZMgK6+6Uk8u06/V8/oS1GHMKYnHX4EPSrGHExlZxKArzb9cAZILLH/sCgYBs2h5zpQcS6EQ2s7dWwVSjCnsd3L/3lcPEsXo5hxx2r3x/kvE8LXB7kr5EkHJsy3r7kZhLnv/f2ZaGHy5isp69lOGJC/9AHI777Il6H+HG/PVArZi+aK+NsPmw5ERcU2SGfiIno4VBNYPjqaYanXLxBPWUu+usgDaPIHcZ3b83kQ==',
        'log'            => [
            'file' => storage_path('logs/alipay.log'),
        ],
    ],

    'wechat' => [
        'app_id'      => '',
        'mch_id'      => '',
        'key'         => '',
        'cert_client' => '',
        'cert_key'    => '',
        'log'         => [
            'file' => storage_path('logs/wechat_pay.log'),
        ],
    ],
];