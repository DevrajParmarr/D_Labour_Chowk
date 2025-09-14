#!/bin/bash

echo "🚀 D Labour Chowk - Automated Deployment Script"
echo "=============================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}Step 1: Checking git status...${NC}"
git status

echo -e "\n${BLUE}Step 2: Adding all changes...${NC}"
git add .

echo -e "\n${BLUE}Step 3: Committing changes...${NC}"
git commit -m "Automated deployment - $(date)"

echo -e "\n${BLUE}Step 4: Pushing to GitHub...${NC}"
git push origin main:master

echo -e "\n${GREEN}✅ Code pushed to GitHub successfully!${NC}"
echo -e "\n${YELLOW}📋 Next Steps:${NC}"
echo "1. Go to https://render.com/dashboard"
echo "2. Find your 'D_Labour_Chowk' service"
echo "3. Click 'Manual Deploy'"
echo "4. Select 'Deploy latest commit'"
echo "5. Click 'Deploy'"
echo "6. Wait 5-7 minutes for deployment"
echo ""
echo -e "${GREEN}🌐 Your live URL: https://d-labour-chowk-vg79.onrender.com${NC}"

echo -e "\n${BLUE}==============================================${NC}"
echo -e "${GREEN}🎉 Deployment script completed!${NC}"