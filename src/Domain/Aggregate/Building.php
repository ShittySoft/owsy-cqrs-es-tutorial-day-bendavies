<?php

declare(strict_types=1);

namespace Building\Domain\Aggregate;

use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIntoBuilding;
use Building\Domain\DomainEvent\UserCheckedOutOfBuilding;
use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;

final class Building extends AggregateRoot
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $users = [];

    public static function new(string $name) : self
    {
        $self = new self();

        $self->recordThat(NewBuildingWasRegistered::occur(
            (string) Uuid::uuid4(),
            [
                'name' => $name
            ]
        ));

        return $self;
    }

    public function checkInUser(string $username)
    {
        if (isset($this->users[$username])) {
            throw new \InvalidArgumentException('user already checked in');
        }

        $this->recordThat(
            UserCheckedIntoBuilding::fromBuildingIdAndUsername($this->uuid, $username)
        );
    }

    public function checkOutUser(string $username)
    {
        if (!isset($this->users[$username])) {
            throw new \InvalidArgumentException('user not checked in');
        }

        $this->recordThat(
            UserCheckedOutOfBuilding::fromBuildingIdAndUsername($this->uuid, $username)
        );
    }

    public function whenNewBuildingWasRegistered(NewBuildingWasRegistered $event)
    {
        $this->uuid = Uuid::fromString($event->aggregateId());
        $this->name = $event->name();
    }

    public function whenUserCheckedIntoBuilding(UserCheckedIntoBuilding $event)
    {
        $this->users[$event->username()] = true;
    }

    public function whenUserCheckedOutOfBuilding(UserCheckedOutOfBuilding $event)
    {
        unset($this->users[$event->username()]);
    }

    /**
     * {@inheritDoc}
     */
    protected function aggregateId() : string
    {
        return (string) $this->uuid;
    }

    /**
     * {@inheritDoc}
     */
    public function id() : string
    {
        return $this->aggregateId();
    }
}
