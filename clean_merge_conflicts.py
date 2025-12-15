import os
import re

def clean_merge_conflicts(file_path):
    """Remove Git merge conflict markers from a file"""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        if '<<<<<<< HEAD' not in content:
            return False
        
        # Pattern to match merge conflicts
        # We'll keep the HEAD version (between <<<<<<< HEAD and =======)
        # and remove the master version (between ======= and >>>>>>> master)
        pattern = r'<<<<<<< HEAD\n(.*?)\n=======\n.*?\n>>>>>>> master'
        
        # Replace with just the HEAD content
        cleaned = re.sub(pattern, r'\1', content, flags=re.DOTALL)
        
        # Also handle cases where HEAD section is empty
        pattern2 = r'<<<<<<< HEAD\n=======\n(.*?)\n>>>>>>> master'
        cleaned = re.sub(pattern2, r'\1', cleaned, flags=re.DOTALL)
        
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(cleaned)
        
        return True
    except Exception as e:
        print(f"Error processing {file_path}: {str(e)}")
        return False

# Process all PHP and Blade files
base_dir = r'd:\Coding\monitoring-suhu-pusvetma-main\monitoring-suhu-pusvetma-main'
files_cleaned = 0

for root, dirs, files in os.walk(base_dir):
    # Skip vendor and node_modules
    if 'vendor' in root or 'node_modules' in root:
        continue
    
    for file in files:
        if file.endswith('.php') or file.endswith('.blade.php'):
            file_path = os.path.join(root, file)
            if clean_merge_conflicts(file_path):
                files_cleaned += 1
                print(f"Cleaned: {file_path}")

print(f"\nTotal files cleaned: {files_cleaned}")
