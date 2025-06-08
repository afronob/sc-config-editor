/**
 * Module de logging global pour le debug de l'intégration
 */
export class GlobalLogger {
    constructor() {
        this.logs = [];
        this.maxLogs = 1000;
        
        // Exposer globalement pour debug
        window.globalLogger = this;
    }

    log(message, level = 'info', module = 'Unknown') {
        const timestamp = new Date().toISOString();
        const logEntry = {
            timestamp,
            level,
            module,
            message
        };
        
        this.logs.push(logEntry);
        
        // Limiter le nombre de logs
        if (this.logs.length > this.maxLogs) {
            this.logs.shift();
        }
        
        // Afficher dans la console avec couleurs
        const colors = {
            'error': 'color: #dc3545; font-weight: bold;',
            'warn': 'color: #ffc107; font-weight: bold;',
            'info': 'color: #007bff;',
            'success': 'color: #28a745; font-weight: bold;',
            'debug': 'color: #6c757d;'
        };
        
        const style = colors[level] || colors.info;
        console.log(`%c[${module}] ${message}`, style);
    }

    error(message, module = 'Unknown') {
        this.log(message, 'error', module);
    }

    warn(message, module = 'Unknown') {
        this.log(message, 'warn', module);
    }

    info(message, module = 'Unknown') {
        this.log(message, 'info', module);
    }

    success(message, module = 'Unknown') {
        this.log(message, 'success', module);
    }

    debug(message, module = 'Unknown') {
        this.log(message, 'debug', module);
    }

    getLogs(level = null, module = null) {
        let filteredLogs = this.logs;
        
        if (level) {
            filteredLogs = filteredLogs.filter(log => log.level === level);
        }
        
        if (module) {
            filteredLogs = filteredLogs.filter(log => log.module === module);
        }
        
        return filteredLogs;
    }

    getLogsAsString(level = null, module = null) {
        const logs = this.getLogs(level, module);
        return logs.map(log => 
            `[${log.timestamp}] [${log.level.toUpperCase()}] [${log.module}] ${log.message}`
        ).join('\n');
    }

    clear() {
        this.logs = [];
        console.clear();
        this.info('Logs cleared', 'GlobalLogger');
    }

    // Méthode pour exporter les logs
    exportLogs() {
        const logsString = this.getLogsAsString();
        const blob = new Blob([logsString], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `integration-logs-${new Date().toISOString().replace(/[:.]/g, '-')}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
}

// Créer l'instance globale
const logger = new GlobalLogger();
export default logger;
