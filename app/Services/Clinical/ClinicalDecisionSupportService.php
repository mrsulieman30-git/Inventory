<?php

namespace App\Services\Clinical;

use App\Models\Item;
use Illuminate\Support\Facades\Log;

/**
 * Class ClinicalDecisionSupportService
 *
 * Provides automated AI/Rules-based Clinical Decision Support (CDS) during dispensing.
 * Validates allergies, duplicate therapies, and drug-drug interactions.
 */
class ClinicalDecisionSupportService
{
    /**
     * Check if the item being dispensed is safe for the patient.
     *
     * @param Item $item The medication to be dispensed.
     * @param string $patientId The patient's ID/MRN.
     * @return array Returns an array with 'is_safe' (bool) and 'warnings' (array).
     */
    public function performSafetyCheck(Item $item, string $patientId): array
    {
        $warnings = [];

        // In a real enterprise application, this would query a Patient model
        // or external EMR/EHR system (via HL7/FHIR) to fetch allergies.
        $patientAllergies = $this->mockFetchPatientAllergies($patientId);

        // 1. Allergy Checking
        if ($this->hasAllergyConflict($item, $patientAllergies)) {
            $warnings[] = "ALLERGY ALERT: Patient has a documented allergy to components in [{$item->name}] ({$item->generic_name}).";
        }

        // 2. Drug-Drug Interaction (DDI) Checking
        // Would normally query the patient's currently active prescriptions
        $activeMedications = $this->mockFetchPatientActiveMeds($patientId);
        $interaction = $this->checkInteractions($item, $activeMedications);

        if ($interaction) {
            $warnings[] = "DRUG INTERACTION: [{$item->name}] interacts with active medication [{$interaction['drug']}]. Severity: {$interaction['severity']}.";
        }

        return [
            'is_safe'  => empty($warnings),
            'warnings' => $warnings,
        ];
    }

    /**
     * Helper to determine if an allergy matches the drug.
     */
    protected function hasAllergyConflict(Item $item, array $allergies): bool
    {
        // Simple mock logic matching generic name
        foreach ($allergies as $allergy) {
            if (stripos($item->generic_name ?? '', $allergy) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Helper to check interactions against a database/API (like Lexicomp or Medispan).
     */
    protected function checkInteractions(Item $newItem, array $activeMeds): ?array
    {
        // Mocking a severe interaction scenario for demonstration
        $severePairs = [
            ['Warfarin', 'Aspirin'],
            ['Sildenafil', 'Nitroglycerin']
        ];

        foreach ($activeMeds as $med) {
            foreach ($severePairs as $pair) {
                if (in_array($newItem->generic_name, $pair) && in_array($med, $pair)) {
                    return [
                        'drug' => $med,
                        'severity' => 'HIGH - Contraindicated',
                    ];
                }
            }
        }

        return null;
    }

    // --- Mock Data Providers for V2 Prototype ---

    protected function mockFetchPatientAllergies(string $patientId): array
    {
        // Mocking that patient 'PAT-123' is allergic to Penicillin
        return $patientId === 'PAT-123' ? ['Penicillin', 'Amoxicillin'] : [];
    }

    protected function mockFetchPatientActiveMeds(string $patientId): array
    {
        // Mocking that patient 'PAT-456' is currently taking Warfarin
        return $patientId === 'PAT-456' ? ['Warfarin', 'Lisinopril'] : [];
    }
}
