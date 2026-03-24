<?php

declare(strict_types=1);

namespace Tests\Unit;

use DF\DigitalKassa\Exceptions\TransportException;
use DF\DigitalKassa\V21\DTO\Shared\AdditionalAttributeDTO;
use DF\DigitalKassa\V21\DTO\Shared\AgentDTO;
use DF\DigitalKassa\V21\DTO\Shared\AnotherAgentDTO;
use DF\DigitalKassa\V21\DTO\Shared\AttorneyDTO;
use DF\DigitalKassa\V21\DTO\Shared\BankPayingAgentDetailsDTO;
use DF\DigitalKassa\V21\DTO\Shared\BankPayingAgentDTO;
use DF\DigitalKassa\V21\DTO\Shared\BankPayingSubagentDTO;
use DF\DigitalKassa\V21\DTO\Shared\CashierDTO;
use DF\DigitalKassa\V21\DTO\Shared\CashlessPaymentsDTO;
use DF\DigitalKassa\V21\DTO\Shared\CommissionAgentDTO;
use DF\DigitalKassa\V21\DTO\Shared\CustomerDTO;
use DF\DigitalKassa\V21\DTO\Shared\MarkingDTO;
use DF\DigitalKassa\V21\DTO\Shared\MoneyTransferOperatorDTO;
use DF\DigitalKassa\V21\DTO\Shared\PayingAgentDetailsDTO;
use DF\DigitalKassa\V21\DTO\Shared\PayingAgentDTO;
use DF\DigitalKassa\V21\DTO\Shared\PayingSubagentDTO;
use DF\DigitalKassa\V21\DTO\Shared\PaymentOperatorDTO;
use DF\DigitalKassa\V21\DTO\Shared\ServiceDTO;
use DF\DigitalKassa\V21\DTO\Shared\SupplierDTO;
use DF\DigitalKassa\V21\Enums\AgentType;
use DF\DigitalKassa\V21\Enums\MarkingItemStatus;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DtoCoverageTest extends TestCase
{
    /** Общие DTO и DTO агентов должны создаваться с ожидаемыми полями и типами. */
    public function test_it_constructs_shared_dtos_and_agent_variants(): void
    {
        $supplier = new SupplierDTO(
            phones: ['+79990000000'],
            name: 'Supplier',
            tin: '5501001001',
        );
        $paymentOperator = new PaymentOperatorDTO(
            phones: ['+79990000001'],
        );
        $payingAgentDetails = new PayingAgentDetailsDTO(
            phones: ['+79990000002'],
        );
        $bankAgentDetails = new BankPayingAgentDetailsDTO(
            phones: ['+79990000003'],
            operation: 'payment_acceptance',
        );
        $moneyTransferOperator = new MoneyTransferOperatorDTO(
            phones: ['+79990000004'],
            name: 'Operator',
            tin: '5501001002',
            address: 'Omsk, Lenina 1',
        );

        $additionalAttribute = new AdditionalAttributeDTO(
            name: 'source',
            value: 'mobile-app',
        );
        $cashier = new CashierDTO(
            name: 'Cashier',
            tin: '123456789012',
        );
        $cashlessPayments = new CashlessPaymentsDTO(
            payment_sum: 100.50,
            payment_method_flag: 4,
            payment_identifiers: 'bank-card',
            additional_info: 'terminal',
        );
        $customer = new CustomerDTO(
            tin: '5501001003',
            name: 'Customer',
        );
        $marking = new MarkingDTO(
            code: '0104601234567890215ABC',
            item_status: MarkingItemStatus::SOLD_UNIT,
            numerator: 1,
            denominator: 2,
        );
        $service = new ServiceDTO(
            callback_url: 'https://example.com/callback',
            receipt_url: 'https://example.com/receipt',
        );

        $anotherAgent = new AnotherAgentDTO($supplier);
        $attorney = new AttorneyDTO($supplier);
        $commissionAgent = new CommissionAgentDTO($supplier);
        $bankPayingAgent = new BankPayingAgentDTO(
            supplier: $supplier,
            paying_agent: $bankAgentDetails,
            money_transfer_operator: $moneyTransferOperator,
        );
        $bankPayingSubagent = new BankPayingSubagentDTO(
            supplier: $supplier,
            paying_agent: $bankAgentDetails,
            money_transfer_operator: $moneyTransferOperator,
        );
        $payingAgent = new PayingAgentDTO(
            supplier: $supplier,
            paying_agent: $payingAgentDetails,
            payment_operator: $paymentOperator,
        );
        $payingSubagent = new PayingSubagentDTO(
            supplier: $supplier,
            paying_agent: $payingAgentDetails,
            payment_operator: $paymentOperator,
        );

        self::assertSame('source', $additionalAttribute->name);
        self::assertSame('Cashier', $cashier->name);
        self::assertSame(100.50, $cashlessPayments->payment_sum);
        self::assertSame('Customer', $customer->name);
        self::assertSame(1, $marking->numerator);
        self::assertSame('https://example.com/callback', $service->callback_url);

        foreach ([
            $anotherAgent,
            $attorney,
            $commissionAgent,
            $bankPayingAgent,
            $bankPayingSubagent,
            $payingAgent,
            $payingSubagent,
        ] as $agent) {
            self::assertInstanceOf(AgentDTO::class, $agent);
        }

        self::assertSame(AgentType::ANOTHER, $anotherAgent->type);
        self::assertSame(AgentType::ATTORNEY, $attorney->type);
        self::assertSame(AgentType::COMMISSION_AGENT, $commissionAgent->type);
        self::assertSame(AgentType::BANK_PAYING_AGENT, $bankPayingAgent->type);
        self::assertSame(AgentType::BANK_PAYING_SUBAGENT, $bankPayingSubagent->type);
        self::assertSame(AgentType::PAYING_AGENT, $payingAgent->type);
        self::assertSame(AgentType::PAYING_SUBAGENT, $payingSubagent->type);
        self::assertSame('payment_acceptance', $bankAgentDetails->operation);
        self::assertSame('Operator', $moneyTransferOperator->name);
        self::assertSame('+79990000001', $paymentOperator->phones[0]);
    }

    /** `TransportException` должен включать в сообщение метод SDK, HTTP-метод и URI. */
    public function test_it_builds_transport_exception_message(): void
    {
        $previous = new RuntimeException('socket closed');

        $exception = new TransportException(
            sdkMethod: 'getCGroupInfo',
            httpMethod: 'GET',
            uri: 'c_groups/12',
            previous: $previous,
        );

        self::assertSame('getCGroupInfo', $exception->sdkMethod);
        self::assertSame('GET', $exception->httpMethod);
        self::assertSame('c_groups/12', $exception->uri);
        self::assertStringContainsString('Transport error while calling getCGroupInfo [GET c_groups/12]', $exception->getMessage());
        self::assertSame($previous, $exception->getPrevious());
    }
}
