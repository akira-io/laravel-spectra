import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '../ui/dialog';
import { FolderOpen } from 'lucide-react';
import Collections from '../Collections';

interface CollectionsModalProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

export default function CollectionsModal({ open, onOpenChange }: CollectionsModalProps) {
  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-2xl max-h-[80vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="text-base">
            <FolderOpen className="h-5 w-5 inline mr-2" />
            Collections
          </DialogTitle>
          <DialogDescription>
            Manage and organize your API request collections
          </DialogDescription>
        </DialogHeader>
        <div className="py-4">
          <Collections />
        </div>
      </DialogContent>
    </Dialog>
  );
}