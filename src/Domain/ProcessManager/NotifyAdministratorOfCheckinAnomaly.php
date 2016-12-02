<?php

declare(strict_types = 1);

namespace Building\Domain\ProcessManager;

use Building\Domain\Command\NotifyAdministratorOfCheckingAnomaly;
use Building\Domain\DomainEvent\CheckInAnomalyDetected;
use Prooph\ServiceBus\CommandBus;
use Rhumsaa\Uuid\Uuid;

class NotifyAdministratorOfCheckinAnomaly
{

    /**
     * @var CommandBus
     */
    private $bus;

    public function __construct(CommandBus $bus)
    {
        $this->bus = $bus;
    }

    public function __invoke(CheckInAnomalyDetected $event)
    {
        $this->bus->dispatch(
            NotifyAdministratorOfCheckingAnomaly::fromBuildingAndUsername(
                Uuid::fromString($event->aggregateId()),
                $event->username()
            )
        );
    }

}
