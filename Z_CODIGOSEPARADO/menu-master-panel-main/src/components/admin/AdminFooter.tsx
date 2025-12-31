import { Save, Eye } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface AdminFooterProps {
  onSave: () => void;
  onPreview: () => void;
  saving?: boolean;
}

export function AdminFooter({ onSave, onPreview, saving }: AdminFooterProps) {
  return (
    <footer className="fixed bottom-0 left-0 right-0 bg-background/80 backdrop-blur-lg border-t border-border p-4 z-50">
      <div className="container max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
        <Button
          variant="outline"
          onClick={onPreview}
          className="w-full sm:w-auto order-2 sm:order-1"
        >
          <Eye className="h-4 w-4 mr-2" />
          Visualizar como Cliente
        </Button>
        
        <Button
          onClick={onSave}
          disabled={saving}
          className="gradient-primary w-full sm:w-auto order-1 sm:order-2"
        >
          <Save className="h-4 w-4 mr-2" />
          {saving ? 'Salvando...' : 'Salvar Configurações'}
        </Button>
      </div>
    </footer>
  );
}
