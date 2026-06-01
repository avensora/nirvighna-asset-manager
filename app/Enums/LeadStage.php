<?php

namespace App\Enums;

enum LeadStage: string
{
    case NewLead      = 'new_lead';
    case Contacted    = 'contacted';
    case Interested   = 'interested';
    case ProposalSent = 'proposal_sent';
    case Won          = 'won';
    case Lost         = 'lost';

    public function label(): string
    {
        return match($this) {
            LeadStage::NewLead      => 'New Lead',
            LeadStage::Contacted    => 'Contacted',
            LeadStage::Interested   => 'Interested',
            LeadStage::ProposalSent => 'Proposal Sent',
            LeadStage::Won          => 'Won',
            LeadStage::Lost         => 'Lost',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            LeadStage::NewLead      => 'bg-secondary-subtle text-secondary',
            LeadStage::Contacted    => 'bg-info-subtle text-info',
            LeadStage::Interested   => 'bg-primary-subtle text-primary',
            LeadStage::ProposalSent => 'bg-warning-subtle text-warning',
            LeadStage::Won          => 'bg-success-subtle text-success',
            LeadStage::Lost         => 'bg-danger-subtle text-danger',
        };
    }

    public function headerClass(): string
    {
        return match($this) {
            LeadStage::NewLead      => 'bg-secondary text-white',
            LeadStage::Contacted    => 'bg-info text-white',
            LeadStage::Interested   => 'bg-primary text-white',
            LeadStage::ProposalSent => 'bg-warning text-dark',
            LeadStage::Won          => 'bg-success text-white',
            LeadStage::Lost         => 'bg-danger text-white',
        };
    }

    public static function pipeline(): array
    {
        return [
            self::NewLead,
            self::Contacted,
            self::Interested,
            self::ProposalSent,
            self::Won,
            self::Lost,
        ];
    }
}
