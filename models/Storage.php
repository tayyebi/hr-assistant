<?php
declare(strict_types=1);

function getDataFilePath(): string {
    return DATA_PATH . '/storage.json';
}

function getDefaultData(): array {
    global $defaultConfig;
    return [
        'employees' => [],
        'teams' => [],
        'messages' => [],
        'unassignedMessages' => [],
        'config' => $defaultConfig
    ];
}

function loadData(): array {
    $filePath = getDataFilePath();
    
    if (!file_exists($filePath)) {
        $data = getDefaultData();
        saveData($data);
        return $data;
    }
    
    $json = file_get_contents($filePath);
    if ($json === false) {
        return getDefaultData();
    }
    
    $data = json_decode($json, true);
    if ($data === null) {
        return getDefaultData();
    }
    
    global $defaultConfig;
    if (!isset($data['config'])) {
        $data['config'] = $defaultConfig;
    } else {
        $data['config'] = array_merge($defaultConfig, $data['config']);
    }
    
    return $data;
}

function saveData(array $data): bool {
    $filePath = getDataFilePath();
    $json = json_encode($data, JSON_PRETTY_PRINT);
    return file_put_contents($filePath, $json) !== false;
}

function getEmployees(): array {
    $data = loadData();
    return $data['employees'] ?? [];
}

function getEmployee(string $id): ?array {
    $employees = getEmployees();
    foreach ($employees as $employee) {
        if ($employee['id'] === $id) {
            return $employee;
        }
    }
    return null;
}

function saveEmployee(array $employee): bool {
    $data = loadData();
    $found = false;
    
    foreach ($data['employees'] as $index => $emp) {
        if ($emp['id'] === $employee['id']) {
            $data['employees'][$index] = $employee;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $data['employees'][] = $employee;
    }
    
    return saveData($data);
}

function deleteEmployee(string $id): bool {
    $data = loadData();
    $data['employees'] = array_filter($data['employees'], function($emp) use ($id) {
        return $emp['id'] !== $id;
    });
    $data['employees'] = array_values($data['employees']);
    
    foreach ($data['teams'] as $index => $team) {
        $data['teams'][$index]['memberIds'] = array_filter($team['memberIds'], function($mid) use ($id) {
            return $mid !== $id;
        });
        $data['teams'][$index]['memberIds'] = array_values($data['teams'][$index]['memberIds']);
    }
    
    return saveData($data);
}

function getTeams(): array {
    $data = loadData();
    return $data['teams'] ?? [];
}

function getTeam(string $id): ?array {
    $teams = getTeams();
    foreach ($teams as $team) {
        if ($team['id'] === $id) {
            return $team;
        }
    }
    return null;
}

function saveTeam(array $team): bool {
    $data = loadData();
    $found = false;
    
    foreach ($data['teams'] as $index => $t) {
        if ($t['id'] === $team['id']) {
            $data['teams'][$index] = $team;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $data['teams'][] = $team;
    }
    
    return saveData($data);
}

function deleteTeam(string $id): bool {
    $data = loadData();
    $data['teams'] = array_filter($data['teams'], function($team) use ($id) {
        return $team['id'] !== $id;
    });
    $data['teams'] = array_values($data['teams']);
    
    foreach ($data['employees'] as $index => $emp) {
        if (isset($emp['teamId']) && $emp['teamId'] === $id) {
            $data['employees'][$index]['teamId'] = null;
        }
    }
    
    return saveData($data);
}

function getMessages(): array {
    $data = loadData();
    return $data['messages'] ?? [];
}

function getMessagesByEmployee(string $employeeId): array {
    $messages = getMessages();
    return array_filter($messages, function($msg) use ($employeeId) {
        return $msg['employeeId'] === $employeeId;
    });
}

function saveMessage(array $message): bool {
    $data = loadData();
    $data['messages'][] = $message;
    return saveData($data);
}

function getUnassignedMessages(): array {
    $data = loadData();
    return $data['unassignedMessages'] ?? [];
}

function getConfig(): array {
    $data = loadData();
    return $data['config'] ?? [];
}

function saveConfig(array $config): bool {
    $data = loadData();
    $data['config'] = $config;
    return saveData($data);
}

function addTeamMember(string $teamId, string $employeeId): bool {
    $data = loadData();
    
    foreach ($data['teams'] as $index => $team) {
        if ($team['id'] === $teamId) {
            if (!in_array($employeeId, $team['memberIds'])) {
                $data['teams'][$index]['memberIds'][] = $employeeId;
            }
            break;
        }
    }
    
    foreach ($data['employees'] as $index => $emp) {
        if ($emp['id'] === $employeeId) {
            $data['employees'][$index]['teamId'] = $teamId;
            break;
        }
    }
    
    return saveData($data);
}

function removeTeamMember(string $teamId, string $employeeId): bool {
    $data = loadData();
    
    foreach ($data['teams'] as $index => $team) {
        if ($team['id'] === $teamId) {
            $data['teams'][$index]['memberIds'] = array_filter($team['memberIds'], function($mid) use ($employeeId) {
                return $mid !== $employeeId;
            });
            $data['teams'][$index]['memberIds'] = array_values($data['teams'][$index]['memberIds']);
            break;
        }
    }
    
    foreach ($data['employees'] as $index => $emp) {
        if ($emp['id'] === $employeeId) {
            $data['employees'][$index]['teamId'] = null;
            break;
        }
    }
    
    return saveData($data);
}

function addTeamAlias(string $teamId, string $alias): bool {
    $data = loadData();
    
    foreach ($data['teams'] as $index => $team) {
        if ($team['id'] === $teamId) {
            if (!in_array($alias, $team['emailAliases'])) {
                $data['teams'][$index]['emailAliases'][] = $alias;
            }
            break;
        }
    }
    
    return saveData($data);
}

function generateId(string $prefix = ''): string {
    return $prefix . '_' . time() . '_' . bin2hex(random_bytes(4));
}
