import { Store, Power } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

interface AdminHeaderProps {
  isOnline: boolean;
  onToggleStatus: () => void;
  saving?: boolean;
}

export function AdminHeader({ isOnline, onToggleStatus, saving }: AdminHeaderProps) {
  return (
    <header className="card-elevated p-6 mb-6 animate-fade-in">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div className="flex items-center gap-4">
          <div className="p-3 rounded-xl gradient-primary">
            <Store className="h-6 w-6 text-primary-foreground" />
          </div>
          <div>
            <h1 className="text-2xl font-bold text-foreground">Painel de Administração</h1>
            <p className="text-muted-foreground text-sm">Gerenciar Cardápio Online</p>
          </div>
        </div>
        
        <div className="flex items-center gap-4">
          <div className={cn(
            "status-badge",
            isOnline ? "status-online" : "status-offline"
          )}>
            <span className={cn(
              "w-2 h-2 rounded-full",
              isOnline ? "bg-success animate-pulse-subtle" : "bg-destructive"
            )} />
            {isOnline ? 'ONLINE' : 'OFFLINE'}
          </div>
          
          <Button
            onClick={onToggleStatus}
            disabled={saving}
            variant={isOnline ? "destructive" : "default"}
            className={cn(
              "gap-2 transition-all duration-300",
              !isOnline && "gradient-primary hover:opacity-90"
            )}
          >
            <Power className="h-4 w-4" />
            {isOnline ? 'Fechar Loja' : 'Abrir Loja'}
          </Button>
        </div>
      </div>
    </header>
  );
}
